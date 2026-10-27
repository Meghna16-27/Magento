<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\Resolver;

use Codilar\GraphQlDemo\Api\GreetingManagementInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class FetchGreetings implements ResolverInterface
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
    ): array {
        $greetings = $this->greetingManagement->getList();

        $result = [];

        foreach ($greetings as $greeting) {
            $result[] = [
                'entity_id' => (int) $greeting->getId(),
                'name' => $greeting->getName(),
                'email' => $greeting->getEmail(),
                'created_at' => $greeting->getCreatedAt(),
            ];
        }

        return $result;
    }
}
