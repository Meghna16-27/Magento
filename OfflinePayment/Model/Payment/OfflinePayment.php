<?php

declare(strict_types=1);

namespace Codilar\OfflinePayment\Model\Payment;

use Magento\Payment\Model\Method\AbstractMethod;

class OfflinePayment extends AbstractMethod
{
    protected $_code = 'offlinepayment';

    protected $_isOffline = true;

    protected $_canUseCheckout = true;

    protected $_canUseInternal = true;

    protected $_canOrder = true;

    protected $_canCapture = false;

    protected $_canRefund = false;

    protected $_canVoid = false;

    protected $_canAuthorize = false;
}
