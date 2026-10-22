<?php

declare(strict_types=1);

namespace Codilar\PincodeValidator\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\ScopeInterface;

class PincodeValidator
{
    private const XML_PATH_ENABLED =
        'pincodevalidator/general/enabled';

    private const XML_PATH_CSV =
        'pincodevalidator/general/pincode_csv';

    private const CACHE_KEY = 'codilar_pincode_validator_allowed';

    private const CACHE_TAG = 'CODILAR_PINCODE_VALIDATOR';

    private const CACHE_LIFETIME = 300;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly DirectoryList $directoryList,
        private readonly File $fileDriver,
        private readonly CacheInterface $cache
    ) {
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isAllowed(
        string $pincode,
        ?int $storeId = null
    ): bool {
        if (!$this->isEnabled($storeId)) {
            return true;
        }

        $pincode = trim($pincode);

        if ($pincode === '') {
            return false;
        }

        $allowedPincodes = $this->getAllowedPincodes($storeId);

        return in_array($pincode, $allowedPincodes, true);
    }

    public function assertAllowed(
        string $pincode,
        ?int $storeId = null
    ): void {
        if (!$this->isEnabled($storeId)) {
            return;
        }

        if (!$this->isAllowed($pincode, $storeId)) {
            throw new LocalizedException(
                __(
                    'Sorry, we cannot deliver to pincode "%1". Please enter a supported delivery pincode.',
                    $pincode !== '' ? $pincode : '(empty)'
                )
            );
        }
    }

    private function getAllowedPincodes(
        ?int $storeId = null
    ): array {
        $cacheKey = self::CACHE_KEY . '_' . ($storeId ?? 0);

        $cached = $this->cache->load($cacheKey);

        if ($cached !== false) {
            $decoded = json_decode($cached, true);

            if (is_array($decoded)) {
                return $decoded;
            }
        }

        $path = $this->scopeConfig->getValue(
            self::XML_PATH_CSV,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!is_string($path) || trim($path) === '') {
            throw new LocalizedException(
                __('Pincode restriction is enabled, but no CSV file is configured.')
            );
        }

        $mediaDirectory = $this->directoryList->getPath(
            DirectoryList::MEDIA
        );

        $allowedDirectory = realpath(
            $mediaDirectory . '/pincode_validator'
        );

        if ($allowedDirectory === false) {
            throw new LocalizedException(
                __('The pincode CSV directory does not exist.')
            );
        }

        $relativePath = str_replace('\\', '/', trim($path));
        $relativePath = ltrim($relativePath, '/');

        // Support either a path relative to media or just a filename.
        $candidate = realpath($mediaDirectory . '/' . $relativePath);

        if (
            $candidate === false
            || !str_starts_with(
                $candidate,
                $allowedDirectory . DIRECTORY_SEPARATOR
            )
            || !$this->fileDriver->isFile($candidate)
            || !$this->fileDriver->isReadable($candidate)
        ) {
            $candidate = realpath(
                $allowedDirectory . '/' . basename($relativePath)
            );
        }

        if (
            $candidate === false
            || !str_starts_with(
                $candidate,
                $allowedDirectory . DIRECTORY_SEPARATOR
            )
            || !$this->fileDriver->isFile($candidate)
            || !$this->fileDriver->isReadable($candidate)
        ) {
            throw new LocalizedException(
                __('The configured pincode CSV file is missing or unreadable.')
            );
        }

        $handle = $this->fileDriver->fileOpen($candidate, 'r');

        if ($handle === false) {
            throw new LocalizedException(
                __('Unable to open the pincode CSV file.')
            );
        }

        $pincodes = [];

        try {
            $firstRow = true;

            while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
                if (!isset($row[0])) {
                    continue;
                }

                $value = trim((string) $row[0]);

                if ($firstRow) {
                    $firstRow = false;

                    // Accept common CSV header names.
                    if (
                        in_array(
                            strtolower($value),
                            ['pincode', 'postcode', 'zipcode', 'zip code'],
                            true
                        )
                    ) {
                        continue;
                    }
                }

                if ($value === '') {
                    continue;
                }

                // Remove a UTF-8 BOM if present in the first value.
                $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
                $value = trim($value);

                if ($value !== '') {
                    $pincodes[] = $value;
                }
            }
        } finally {
            $this->fileDriver->fileClose($handle);
        }

        if ($pincodes === []) {
            throw new LocalizedException(
                __('The configured pincode CSV contains no usable pincodes.')
            );
        }

        $pincodes = array_values(array_unique($pincodes));

        $this->cache->save(
            json_encode($pincodes, JSON_THROW_ON_ERROR),
            $cacheKey,
            [self::CACHE_TAG],
            self::CACHE_LIFETIME
        );

        return $pincodes;
    }
}
