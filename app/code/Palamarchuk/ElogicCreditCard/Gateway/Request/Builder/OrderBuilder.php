<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request\Builder;

use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Palamarchuk\ElogicCreditCard\Gateway\Request\OrderIdProvider;
use Palamarchuk\ElogicCreditCard\Api\Sdk\RequestFieldsInterface as RequestFields;

/**
 * Class OrderBuilder
 */
class OrderBuilder implements BuilderInterface
{
    /**
     * @var OrderIdProvider
     */
    private $orderIdProvider;

    /**
     * OrderBuilder constructor.
     * @param OrderIdProvider $orderIdProvider
     */
    public function __construct(OrderIdProvider $orderIdProvider)
    {
        $this->orderIdProvider = $orderIdProvider;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        /** @var OrderAdapterInterface $order */
        $order = $buildSubject['payment']->getOrder();
        return [
            RequestFields::ORDER_ID => $this->orderIdProvider->get($order),
        ];
    }
}
