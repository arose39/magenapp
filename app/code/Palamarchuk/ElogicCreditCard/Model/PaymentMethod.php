<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Model;

//todo Magento\Payment\Model\Method\Adapter try
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'testpayment';


}
