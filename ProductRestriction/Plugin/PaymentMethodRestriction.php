<?php
namespace Codilar\ProductRestriction\Plugin;

use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use Codilar\ProductRestriction\Model\ResourceModel\ProductRestriction\CollectionFactory as RestrictionCollectionFactory;

class PaymentMethodRestriction
{
    protected $cart;
    protected $restrictionCollectionFactory;

    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        RestrictionCollectionFactory $restrictionCollectionFactory
    ) {
        $this->cart = $cart;
        $this->restrictionCollectionFactory = $restrictionCollectionFactory;
    }

    public function afterGetAvailableMethods(MethodList $subject, array $result, ?CartInterface $quote = null)
    {
        if (!$quote) {
            return $result;
        }

        $restrictedSkus = [];
        $collection = $this->restrictionCollectionFactory->create();
        foreach ($collection as $item) {
            $restrictedSkus[] = $item->getSku();
        }

        $hasRestrictedItem = false;
        foreach ($quote->getAllItems() as $item) {
            if (in_array($item->getSku(), $restrictedSkus)) {
                $hasRestrictedItem = true;
                break;
            }
        }

        if ($hasRestrictedItem) {
            $filteredMethods = [];
            foreach ($result as $method) {
                if ($method->getCode() === 'checkmo') {
                    $filteredMethods[] = $method;
                }
            }
            return $filteredMethods;
        }

        return $result;
    }
}
