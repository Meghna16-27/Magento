<?php
namespace Codilar\ProductRestriction\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductRestriction extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('codilar_product_restriction', 'entity_id');
    }
}
