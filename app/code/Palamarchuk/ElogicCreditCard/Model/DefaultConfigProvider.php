<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Palamarchuk\ElogicCreditCard\Api\Data\PaymentMethodCodeInterface;

/**
 * Class DefaultConfigProvider
 */
class DefaultConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * DefaultConfigProvider constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                PaymentMethodCodeInterface::CODE => [
                    'connection_type' => $this->config->getValue('connection_type'),
                ]
            ]
        ];
    }
}
