<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Model;
/**
 * Pay In Store payment method model
 */
class RedirectPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'elogic_redirect_payment';
    protected $_canRefund = true;
    protected $_canFetchTransactionInfo = true;
    protected $_isGateway = true;
    protected $_canOrder = true;


    protected $_canAuthorize = true;

    protected $_canCapture = true;


    protected $_canVoid = true;

    protected $_canUseInternal = false;

    protected $_canUseCheckout = true;

    protected $_canReviewPayment = true;



    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
    }
}
