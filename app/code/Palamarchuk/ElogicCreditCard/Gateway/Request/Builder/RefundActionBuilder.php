<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request\Builder;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Palamarchuk\ElogicCreditCard\Api\Data\PaymentActionInterface;
use Palamarchuk\ElogicCreditCard\Api\Sdk\RequestFieldsInterface as RequestFields;

/**
 * Class RefundActionBuilder
 */
class RefundActionBuilder implements BuilderInterface
{

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            RequestFields::ACTION => PaymentActionInterface::REFUND,
        ];
    }
}
