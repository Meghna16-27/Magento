<?php

declare(strict_types=1);

namespace Codilar\PincodeValidator\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\File;
use Magento\Framework\Exception\LocalizedException;

class PincodeCsv extends File
{
    protected function _getAllowedExtensions(): array
    {
        return ['csv'];
    }

    protected function _beforeSave()
    {
        $value = $this->getValue();

        // Validate the newly uploaded temporary file, if present.
        $files = $_FILES ?? [];
        $file = $this->findUploadedFile($files);

        if ($file !== null && !empty($file['tmp_name'])) {
            $this->validateCsv($file['tmp_name']);
        }

        return parent::_beforeSave();
    }

    private function findUploadedFile(array $files): ?array
    {
        // Magento may use either a simple field key or nested
        // configuration field keys depending on the upload renderer.
        foreach ($files as $file) {
            if (!is_array($file)) {
                continue;
            }

            if (
                isset($file['tmp_name'])
                && is_string($file['tmp_name'])
                && $file['tmp_name'] !== ''
            ) {
                $name = $file['name'] ?? '';

                if (is_string($name) &&
                    strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'csv'
                ) {
                    return $file;
                }
            }

            $nested = $this->findUploadedFile($file);

            if ($nested !== null) {
                return $nested;
            }
        }

        return null;
    }

    private function validateCsv(string $temporaryPath): void
    {
        if (!is_uploaded_file($temporaryPath) || !is_readable($temporaryPath)) {
            throw new LocalizedException(
                __('The uploaded CSV file could not be read.')
            );
        }

        $handle = fopen($temporaryPath, 'r');

        if ($handle === false) {
            throw new LocalizedException(
                __('Unable to open the uploaded CSV file.')
            );
        }

        try {
            $firstRow = true;
            $validCount = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (!isset($row[0])) {
                    continue;
                }

                $pincode = trim((string) $row[0]);

                if ($firstRow) {
                    $firstRow = false;

                    if (strtolower($pincode) === 'pincode') {
                        continue;
                    }
                }

                if ($pincode === '') {
                    continue;
                }

                // Only the first column is used.
                if (str_contains($pincode, "\0")) {
                    throw new LocalizedException(
                        __('The CSV contains an invalid pincode.')
                    );
                }

                $validCount++;
            }

            if ($validCount === 0) {
                throw new LocalizedException(
                    __('The CSV must contain at least one pincode.')
                );
            }
        } finally {
            fclose($handle);
        }
    }
}
