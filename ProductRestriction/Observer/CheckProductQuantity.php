<?php
namespace Codilar\ProductRestriction\Observer;

use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory as RestrictionCollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckProductQuantity implements ObserverInterface
{
    protected $restrictionCollectionFactory;

    public function __construct(RestrictionCollectionFactory $restrictionCollectionFactory)
    {
        $this->restrictionCollectionFactory = $restrictionCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getData('product');
        $request = $observer->getEvent()->getData('request');

        if (!$product) {
            return;
        }

        $sku = $product->getSku();
        $qty = $request ? (int)$request->getParam('qty', 1) : 1;

        if (!$sku) {
            return;
        }

        $collection = $this->restrictionCollectionFactory->create();
        $collection->addFieldToFilter('sku', $sku);
        $restriction = $collection->getFirstItem();

        if ($restriction && $restriction->getId()) {
            $maxQty = (int)$restriction->getMaxQty();
            if ($maxQty > 0 && $qty > $maxQty) {
                throw new LocalizedException(
                    __('You cannot purchase more than %1 quantity for this restricted product.', $maxQty)
                );
            }
        }
    }
}
