<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request\Builder;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Palamarchuk\ElogicCreditCard\Api\Sdk\RequestFieldsInterface as RequestFields;
use Palamarchuk\ElogicCreditCard\Api\Sdk\VersionInterface;
use Palamarchuk\ElogicCreditCard\Gateway\Config;

/**
 * Class GeneralBuilder
 */
class GeneralBuilder implements BuilderInterface
{
    /**
     * @var Config
     */
    private $config;


    /**
     * GeneralBuilder constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            RequestFields::VERSION => VersionInterface::VERSION,
            RequestFields::PUBLIC_KEY => $this->config->getPublicKey(),
        ];
    }
}
