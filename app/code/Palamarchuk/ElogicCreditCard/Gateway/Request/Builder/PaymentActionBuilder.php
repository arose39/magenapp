<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request\Builder;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Palamarchuk\ElogicCreditCard\Gateway\Request\PaymentActionProvider;
use Palamarchuk\ElogicCreditCard\Api\Sdk\RequestFieldsInterface as RequestFields;

/**
 * Class PaymentActionBuilder
 */
class PaymentActionBuilder implements BuilderInterface
{
    /**
     * @var PaymentActionProvider
     */
    private $actionProvider;

    /**
     * PaymentActionBuilder constructor.
     * @param PaymentActionProvider $actionProvider
     */
    public function __construct(PaymentActionProvider $actionProvider)
    {
        $this->actionProvider = $actionProvider;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            RequestFields::ACTION => $this->actionProvider->getPaymentAction(),
        ];
    }
}
