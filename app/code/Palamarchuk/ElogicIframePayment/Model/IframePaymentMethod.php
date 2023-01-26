<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicIframePayment\Model;

class IframePaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'elogic_iframe_payment';
    protected $_canRefund = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;
    protected $_canUseCheckout = true;


    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->_canRefund) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }
        //todo write refund

        return $this;
    }
}
