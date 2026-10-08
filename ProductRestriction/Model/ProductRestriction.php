<?php
namespace Codilar\ProductRestriction\Model;

use Codilar\ProductRestriction\Api\Data\ProductRestrictionInterface;
use Magento\Framework\Model\AbstractModel;

class ProductRestriction extends AbstractModel implements ProductRestrictionInterface
{
    protected function _construct()
    {
        $this->_init(\Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction::class);
    }

    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    public function getMaxQty()
    {
        return $this->getData(self::MAX_QTY);
    }

    public function setMaxQty($maxQty)
    {
        return $this->setData(self::MAX_QTY, $maxQty);
    }
}
