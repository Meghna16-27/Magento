<?php

declare(strict_types=1);

namespace Codilar\ProductEnquiry\Model\Resolver;

use Codilar\ProductEnquiry\Api\EnquiryRepositoryInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductEnquiries implements ResolverInterface
{
    public function __construct(
        private readonly EnquiryRepositoryInterface $enquiryRepository
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $sku = $args['sku'] ?? null;
        // Fetch enquiries using repository
        $enquiries = $this->enquiryRepository->getList($sku);

        $result = [];

        foreach ($enquiries as $enquiry) {
            $result[] = [
                'entity_id' => (int) $enquiry->getId(),
                'name' => $enquiry->getName(),
                'address' => $enquiry->getAddress(),
                'email' => $enquiry->getEmail(),
                'sku' => $enquiry->getSku(),
                'qty' => (int) $enquiry->getQty(),
                'created_at' => $enquiry->getCreatedAt()
            ];
        }

        return $result;
    }
}
