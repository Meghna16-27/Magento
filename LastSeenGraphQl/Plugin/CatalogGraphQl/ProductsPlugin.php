<?php

declare(strict_types=1);

namespace Codilar\LastSeenGraphQl\Plugin\CatalogGraphQl;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\Resolver\Products;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Psr\Log\LoggerInterface;

class ProductsPlugin
{
    public function __construct(
        private readonly OrderCollectionFactory $orderCollectionFactory,
        private readonly LoggerInterface $logger
    ) {
    }

    public function aroundResolve(
        Products $subject,
        callable $proceed,
        Field $field,
                 $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): array {
        $this->logger->info(
            '[LastSeenGraphQl] ProductsPlugin started'
        );

        // 1. Execute Magento's original products resolver.
        $result = $proceed(
            $field,
            $context,
            $info,
            $value,
            $args
        );

        if (empty($result['items'])) {
            return $result;
        }

        // 2. Find the latest order across ALL customers.
        $orders = $this->orderCollectionFactory->create();
        $orders->setOrder('created_at', 'DESC');
        $orders->setOrder('entity_id', 'DESC');
        $orders->setPageSize(1);

        $latestOrder = $orders->getFirstItem();

        $this->logger->info(
            '[LastSeenGraphQl] Global latest order ID: ' .
            ($latestOrder->getId() ?: 'NO ORDER FOUND')
        );

        // 3. Collect product IDs from that order.
        $orderedProductIds = [];

        if ($latestOrder->getId()) {
            foreach ($latestOrder->getAllItems() as $orderItem) {
                // Avoid counting child items separately.
                if ($orderItem->getParentItemId()) {
                    continue;
                }

                $productId = (int) $orderItem->getProductId();

                if ($productId > 0) {
                    $orderedProductIds[$productId] = true;
                }
            }
        }

        $this->logger->info(
            '[LastSeenGraphQl] Global ordered product IDs: ' .
            implode(',', array_keys($orderedProductIds))
        );

        // 4. Calculate last_seen for each returned product.
        foreach ($result['items'] as &$item) {
            $productId = 0;
            $product = null;

            if (
                isset($item['model'])
                && $item['model'] instanceof Product
            ) {
                $product = $item['model'];
                $productId = (int) $product->getId();
            } elseif (isset($item['entity_id'])) {
                $productId = (int) $item['entity_id'];
            }

            $lastSeen = $productId > 0
                && isset($orderedProductIds[$productId]);

            // Make the value available in the GraphQL result.
            $item['last_seen'] = $lastSeen;

            // Make it available to the custom field resolver too.
            if ($product instanceof Product) {
                $product->setData('last_seen', $lastSeen);
            }

            $this->logger->info(
                '[LastSeenGraphQl] Product ID: ' . $productId .
                ' | last_seen: ' . ($lastSeen ? 'true' : 'false')
            );
        }
        unset($item);

        $this->logger->info(
            '[LastSeenGraphQl] ProductsPlugin finished'
        );

        return $result;
    }
}
