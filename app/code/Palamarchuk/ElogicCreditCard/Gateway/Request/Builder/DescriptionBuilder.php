<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request\Builder;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Palamarchuk\ElogicCreditCard\Api\Sdk\RequestFieldsInterface as RequestFields;

/**
 * Class DescriptionBuilder
 */
class DescriptionBuilder implements BuilderInterface
{
    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            RequestFields::DESCRIPTION => 'CreditCard for Magento 2',
        ];
    }
}
