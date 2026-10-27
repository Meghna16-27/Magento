<?php

declare(strict_types=1);

namespace Codilar\GraphQlDemo\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Greeting extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init(
            'codilar_graphql_greeting',
            'entity_id'
        );
    }
}
