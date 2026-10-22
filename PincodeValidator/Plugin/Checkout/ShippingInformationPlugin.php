<?php

declare(strict_types=1);

namespace Codilar\PincodeValidator\Plugin\Checkout;

use Codilar\PincodeValidator\Model\PincodeValidator;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Api\ShippingInformationManagementInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Throwable;

class ShippingInformationPlugin
{
    public function __construct(
        private readonly PincodeValidator $pincodeValidator,
        private readonly LoggerInterface $logger
    ) {
    }

    public function beforeSaveAddressInformation(
        ShippingInformationManagementInterface $subject,
                                               $cartId,
        ShippingInformationInterface $addressInformation
    ): ?array {
        try {
            $enabled = $this->pincodeValidator->isEnabled();

            $this->logger->info(
                'PincodeValidator: Shipping validation started.',
                [
                    'cart_id' => $cartId,
                    'validation_enabled' => $enabled,
                ]
            );

            // Do not restrict checkout when the feature is disabled.
            if (!$enabled) {
                return null;
            }

            $address = $addressInformation->getShippingAddress();

            if ($address === null) {
                throw new LocalizedException(
                    __('Please provide a shipping address.')
                );
            }

            $postcode = trim((string) $address->getPostcode());

            if ($postcode === '') {
                throw new LocalizedException(
                    __('Please enter a shipping pincode.')
                );
            }

            $this->logger->info(
                'PincodeValidator: Checking shipping postcode.',
                [
                    'cart_id' => $cartId,
                    'postcode_is_empty' => false,
                ]
            );

            $this->pincodeValidator->assertAllowed($postcode);

            $this->logger->info(
                'PincodeValidator: Shipping postcode accepted.',
                ['cart_id' => $cartId]
            );

            return null;
        } catch (Throwable $e) {
            $this->logger->error(
                'PincodeValidator: Shipping validation failed.',
                [
                    'cart_id' => $cartId,
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            );

            throw $e;
        }
    }
}
