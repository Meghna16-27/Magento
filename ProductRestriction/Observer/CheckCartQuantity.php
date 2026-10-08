<?php
namespace Codilar\ProductRestriction\Observer;

use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory as RestrictionCollectionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class CheckCartQuantity implements ObserverInterface
{
    protected $restrictionCollectionFactory;

    public function __construct(RestrictionCollectionFactory $restrictionCollectionFactory)
    {
        $this->restrictionCollectionFactory = $restrictionCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote || $quote->isVirtual()) {
            return;
        }

        $collection = $this->restrictionCollectionFactory->create();
        $restrictions = [];
        foreach ($collection as $restriction) {
            $restrictions[$restriction->getSku()] = (int)$restriction->getMaxQty();
        }

        if (empty($restrictions)) {
            return;
        }

        foreach ($quote->getAllItems() as $item) {
            $sku = $item->getSku();
            if (isset($restrictions[$sku])) {
                $maxQty = $restrictions[$sku];
                if ($item->getQty() > $maxQty) {
                    throw new LocalizedException(
                        __('The maximum allowed quantity for product "%1" is %2.', $item->getName(), $maxQty)
                    );
                }
            }
        }
    }
}
