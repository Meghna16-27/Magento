<?php

declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model\Resolver;

use Codilar\ProductEnquiry\Api\Data\EnquiryInterfaceFactory;
use Codilar\ProductEnquiry\Api\EnquiryRepositoryInterface;
use Codilar\ProductEnquiry\Logger\Logger;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SubmitProductEnquiry implements ResolverInterface
{
    public function __construct(
        private readonly EnquiryInterfaceFactory $enquiryFactory,
        private readonly EnquiryRepositoryInterface $enquiryRepository,
        private readonly Logger $logger
    ) {
    }

    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $input = $args['input'] ?? [];

        // Log the incoming raw request arguments for debugging purposes
        $this->logger->info('Processing product enquiry submission with input data: ' . json_encode($input));

        $name = trim($input['name'] ?? '');
        $address = trim($input['address'] ?? '');
        $email = trim($input['email'] ?? '');
        $sku = trim($input['sku'] ?? '');
        $qty = $input['qty'] ?? 0;

        // Validate required fields
        if (
            $name === '' ||
            $address === '' ||
            $email === '' ||
            $sku === ''
        ) {
            $this->logger->warning('Product enquiry validation failed: Missing required fields.');
            throw new GraphQlInputException(
                __('All required fields must be filled.')
            );
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logger->warning(sprintf('Product enquiry validation failed: Invalid email format (%s)', $email));
            throw new GraphQlInputException(
                __('Please enter a valid email address.')
            );
        }

        // Validate quantity
        if (!is_int($qty) || $qty <= 0) {
            $this->logger->warning(sprintf('Product enquiry validation failed: Invalid quantity (%s)', json_encode($qty)));
            throw new GraphQlInputException(
                __('Quantity must be greater than zero.')
            );
        }

        try {
            $enquiry = $this->enquiryFactory->create();

            $enquiry->setName($name);
            $enquiry->setAddress($address);
            $enquiry->setEmail($email);
            $enquiry->setSku($sku);
            $enquiry->setQty($qty);

            $this->enquiryRepository->save($enquiry);

            $this->logger->info(
                sprintf(
                    'Product enquiry saved successfully for SKU: %s by %s',
                    $sku,
                    $email
                )
            );

            return [
                'success' => true,
                'message' => (string) __('Enquiry submitted successfully.')
            ];
        } catch (\Throwable $e) {
            $this->logger->error(
                'Error saving product enquiry: ' . $e->getMessage(),
                ['exception' => $e]
            );

            throw new GraphQlInputException(
                __('Unable to submit the product enquiry. Please try again.')
            );
        }
    }
}
