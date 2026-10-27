<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model;

use Codilar\GraphQlDemo\Api\Data\GreetingInterface;
use Codilar\GraphQlDemo\Api\GreetingManagementInterface;
use Codilar\GraphQlDemo\Model\ResourceModel\Greeting as GreetingResource;
use Codilar\GraphQlDemo\Model\ResourceModel\Greeting\CollectionFactory as GreetingCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class GreetingManagement implements GreetingManagementInterface
{
    public function __construct(
        private readonly GreetingFactory $greetingFactory,
        private readonly GreetingResource $greetingResource,
        private readonly GreetingCollectionFactory $greetingCollectionFactory
    ) {
    }

    public function create(
        string $name,
        string $email
    ): GreetingInterface {
        $greeting = $this->greetingFactory->create();
        if (trim($name) === '') {
            throw new CouldNotSaveException(__('Name cannot be empty.'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new CouldNotSaveException(__('Email is invalid.'));
        }

        $greeting->setName($name);
        $greeting->setEmail($email);

        try {
            $this->greetingResource->save($greeting);
        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the greeting record.'),
                $exception
            );
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the greeting record.'),
                $exception
            );
        }

        return $greeting;
    }
    public function getList(): array
    {
        $collection = $this->greetingCollectionFactory->create();

        return $collection->getItems();
    }

    public function update(
        int $id,
        string $name,
        string $email
    ): GreetingInterface {
        $greeting = $this->greetingFactory->create();
        $this->greetingResource->load($greeting, $id);

        if (!$greeting->getId()) {
            throw new NoSuchEntityException(
                __('Greeting with ID %1 does not exist.', $id)
            );
        }

        $greeting->setName($name);
        $greeting->setEmail($email);

        $this->greetingResource->save($greeting);

        return $greeting;
    }
    public function delete(int $id): bool
    {
        $greeting = $this->greetingFactory->create();

        $this->greetingResource->load($greeting, $id);

        if (!$greeting->getId()) {
            throw new NoSuchEntityException(
                __('Greeting with ID %1 does not exist.', $id)
            );
        }

        $this->greetingResource->delete($greeting);

        return true;
    }
}
