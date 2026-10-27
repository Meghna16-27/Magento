<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CodilarMessage implements ResolverInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        $productId = (int) $args['productId'];
        $product = $this->productRepository->getById($productId);

        return [
            'message' => $product->getName()
        ];
    }
}
