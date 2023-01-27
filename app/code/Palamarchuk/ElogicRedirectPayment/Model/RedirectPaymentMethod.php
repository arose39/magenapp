<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Model;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Payment\Model\Method\Logger;

class RedirectPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'elogic_redirect_payment';
    protected $_canRefund = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canUseCheckout = true;


    public function __construct(
        private Refund $refund,
        \Magento\Framework\Model\Context                        $context,
        \Magento\Framework\Registry                             $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory,
        \Magento\Payment\Helper\Data                            $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig,
        Logger                                                  $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection = null,
        array                                                   $data = [],
        DirectoryHelper                                         $directory = null
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data,
            $directory
        );
    }


    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        if (!$this->_canRefund) {
            throw new \Magento\Framework\Exception\LocalizedException(__('The refund action is not available.'));
        }

        $info = $this->refund->refund($payment->getOrder());
        $payment->setAdditionalInformation('raw_details_info', $info);

        return $this;
    }
}
