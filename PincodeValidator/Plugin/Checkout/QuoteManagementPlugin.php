<?php

declare(strict_types=1);

namespace Codilar\PincodeValidator\Plugin\Checkout;

use Codilar\PincodeValidator\Model\PincodeValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;
use Psr\Log\LoggerInterface;
use Throwable;

class QuoteManagementPlugin
{
    public function __construct(
        private readonly PincodeValidator $pincodeValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function beforeSubmit(
        QuoteManagement $subject,
        Quote $quote,
                        $orderData = []
    ): array {
        $storeId = (int) $quote->getStoreId();

        $this->logger->info(
            'PincodeValidator: QuoteManagementPlugin beforeSubmit started.',
            [
                'quote_id' => $quote->getId(),
                'store_id' => $storeId,
            ]
        );

        try {
            if (!$this->pincodeValidator->isEnabled($storeId)) {
                $this->logger->info(
                    'PincodeValidator: Validation is disabled.',
                    ['store_id' => $storeId]
                );

                return [$quote, $orderData];
            }

            $address = $quote->getIsVirtual()
                ? $quote->getBillingAddress()
                : $quote->getShippingAddress();

            if ($address === null) {
                throw new LocalizedException(
                    __('Please provide a shipping address.')
                );
            }

            $postcode = trim((string) $address->getPostcode());

            $this->logger->info(
                'PincodeValidator: Checking quote postcode.',
                [
                    'quote_id' => $quote->getId(),
                    'store_id' => $storeId,
                    'postcode' => $postcode,
                ]
            );

            if ($postcode === '') {
                throw new LocalizedException(
                    __('Please enter a shipping pincode.')
                );
            }

            $this->pincodeValidator->assertAllowed(
                $postcode,
                $storeId
            );

            $this->logger->info(
                'PincodeValidator: Quote postcode accepted.',
                [
                    'quote_id' => $quote->getId(),
                    'store_id' => $storeId,
                    'postcode' => $postcode,
                ]
            );

            return [$quote, $orderData];
        } catch (Throwable $e) {
            $this->logger->error(
                'PincodeValidator: QuoteManagementPlugin failed.',
                [
                    'quote_id' => $quote->getId(),
                    'store_id' => $storeId,
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]
            );

            throw $e;
        }
    }
}
