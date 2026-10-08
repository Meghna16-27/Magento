<?php
namespace Codilar\ProductRestriction\Api;

use Codilar\ProductRestriction\Api\Data\ProductRestrictionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface ProductRestrictionRepositoryInterface
{
    public function save(ProductRestrictionInterface $productRestriction);

    public function getById($entityId);

    public function getBySku($sku);

    public function getList(SearchCriteriaInterface $searchCriteria);

    public function delete(ProductRestrictionInterface $productRestriction);

    public function deleteById($entityId);
}
