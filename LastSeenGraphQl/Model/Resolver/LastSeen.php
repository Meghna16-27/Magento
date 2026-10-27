<?php
declare(strict_types=1);

namespace Codilar\LastSeenGraphQl\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class LastSeen implements ResolverInterface
{
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): bool {
        if (
            isset($value['model'])
            && $value['model'] instanceof Product
        ) {
            return (bool) $value['model']->getData('last_seen');
        }

        return (bool) ($value['last_seen'] ?? false);
    }
}
