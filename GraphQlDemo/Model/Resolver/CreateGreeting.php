<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\Resolver;

use Codilar\GraphQlDemo\Api\GreetingManagementInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateGreeting implements ResolverInterface
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
        $args = $args ?? [];

        $name = trim((string) ($args['name'] ?? ''));
        $email = trim((string) ($args['email'] ?? ''));

        if ($name === '') {
            throw new GraphQlInputException(
                __('Name is required.')
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new GraphQlInputException(
                __('Please provide a valid email address.')
            );
        }

        $greeting = $this->greetingManagement->create(
            $name,
            $email
        );

        return [
            'entity_id' => (int) $greeting->getId(),
            'name' => $greeting->getName(),
            'email' => $greeting->getEmail(),
            'created_at' => $greeting->getCreatedAt(),
        ];
    }
}
