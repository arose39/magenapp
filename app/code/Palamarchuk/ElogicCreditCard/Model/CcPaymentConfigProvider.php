<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfig;

class CcPaymentConfigProvider implements ConfigProviderInterface
{
    /**
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        Source                          $assetSource
    )
    {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
     * @var string[]
     */
    protected $_methodCode = 'cc_payment';

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'cc_payment' => [
                    'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' =>  [$this->_methodCode => true],
                ]
            ]
        ];
    }
}
