<?php
namespace Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(
            \Codilar\ProductRestriction\Model\ProductRestriction::class,
            \Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction::class
        );
    }
}
