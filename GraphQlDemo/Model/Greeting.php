<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model;

use Codilar\GraphQlDemo\Api\Data\GreetingInterface;
use Codilar\GraphQlDemo\Model\ResourceModel\Greeting as GreetingResource;
use Magento\Framework\Model\AbstractModel;

class Greeting extends AbstractModel implements GreetingInterface
{
    protected function _construct(): void
    {
        $this->_init(GreetingResource::class);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function getName(): ?string
    {
        $value = $this->getData(self::NAME);

        return $value === null ? null : (string) $value;
    }

    public function setName(string $name): GreetingInterface
    {
        return $this->setData(self::NAME, $name);
    }

    public function getEmail(): ?string
    {
        $value = $this->getData(self::EMAIL);

        return $value === null ? null : (string) $value;
    }

    public function setEmail(string $email): GreetingInterface
    {
        return $this->setData(self::EMAIL, $email);
    }

    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);

        return $value === null ? null : (string) $value;
    }
}
