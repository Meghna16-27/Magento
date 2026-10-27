<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Api;

use Codilar\GraphQlDemo\Api\Data\GreetingInterface;

interface GreetingManagementInterface
{
    /**
     * Create and save a greeting record.
     */
    public function create(
        string $name,
        string $email
    ): GreetingInterface;

    public function getList(): array;

    public function update(
        int $id,
        string $name,
        string $email
    ): GreetingInterface;
    public function delete(int $id): bool;
}
