<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\Resolver;

use Codilar\GraphQlDemo\Api\GreetingManagementInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class DeleteGreeting implements ResolverInterface
{
    public function __construct(
        private readonly GreetingManagementInterface $greetingManagement
    ) {
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ): bool {
        return $this->greetingManagement->delete(
            (int) $args['id']
        );
    }
}
