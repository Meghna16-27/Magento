<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\ResourceModel\Greeting;

use Codilar\GraphQlDemo\Model\Greeting as GreetingModel;
use Codilar\GraphQlDemo\Model\ResourceModel\Greeting as GreetingResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct(): void
    {
        $this->_init(
            GreetingModel::class,
            GreetingResource::class
        );
    }
}
