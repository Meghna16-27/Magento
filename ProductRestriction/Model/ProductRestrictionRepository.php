<?php
namespace Codilar\ProductRestriction\Model;

use Codilar\ProductRestriction\Api\Data\ProductRestrictionInterface;
use Codilar\ProductRestriction\Api\Data\ProductRestrictionInterfaceFactory;
use Codilar\ProductRestriction\Api\ProductRestrictionRepositoryInterface;
use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction as ResourceProductRestriction;
use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductRestrictionRepository implements ProductRestrictionRepositoryInterface
{
    protected $resource;
    protected $productRestrictionFactory;
    protected $collectionFactory;
    protected $collectionProcessor;

    public function __construct(
        ResourceProductRestriction $resource,
        ProductRestrictionInterfaceFactory $productRestrictionFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->productRestrictionFactory = $productRestrictionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(ProductRestrictionInterface $productRestriction)
    {
        try {
            $this->resource->save($productRestriction);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $productRestriction;
    }

    public function getById($entityId)
    {
        $productRestriction = $this->productRestrictionFactory->create();
        $this->resource->load($productRestriction, $entityId);
        if (!$productRestriction->getId()) {
            throw new NoSuchEntityException(__('Product restriction with id "%1" does not exist.', $entityId));
        }
        return $productRestriction;
    }

    public function getBySku($sku)
    {
        $productRestriction = $this->productRestrictionFactory->create();
        $this->resource->load($productRestriction, $sku, 'sku');
        if (!$productRestriction->getId()) {
            throw new NoSuchEntityException(__('Product restriction with SKU "%1" does not exist.', $sku));
        }
        return $productRestriction;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        // Add collection result handling if needed
        return $collection;
    }

    public function delete(ProductRestrictionInterface $productRestriction)
    {
        try {
            $this->resource->delete($productRestriction);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById($entityId)
    {
        return $this->delete($this->getById($entityId));
    }
}
