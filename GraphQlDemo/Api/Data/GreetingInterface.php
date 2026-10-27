<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Api\Data;

interface GreetingInterface
{
    public const ENTITY_ID = 'entity_id';
    public const NAME = 'name';
    public const EMAIL = 'email';
    public const CREATED_AT = 'created_at';

    public function getId();

    public function getName(): ?string;

    public function setName(string $name): GreetingInterface;

    public function getEmail(): ?string;

    public function setEmail(string $email): GreetingInterface;

    public function getCreatedAt(): ?string;
}
