<?php
namespace Codilar\ProductRestriction\Api\Data;

interface ProductRestrictionInterface
{
    const ENTITY_ID = 'entity_id';
    const SKU = 'sku';
    const MAX_QTY = 'max_qty';

    public function getEntityId();
    public function setEntityId($entityId);

    public function getSku();
    public function setSku($sku);

    public function getMaxQty();
    public function setMaxQty($maxQty);
}
