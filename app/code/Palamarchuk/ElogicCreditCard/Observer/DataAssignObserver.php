<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Observer;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Sales\Model\Order\Payment;
use Palamarchuk\ElogicCreditCard\Api\Data\CardPaymentInterface;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @var array
     */
    private $ccKeys = [
        CardPaymentInterface::TYPE,
        CardPaymentInterface::EXPIRATION_YEAR,
        CardPaymentInterface::EXPIRATION_MONTH,
        CardPaymentInterface::NUMBER,
        CardPaymentInterface::CVV
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData('additional_data');
        /** @var InfoInterface|Payment $paymentInfo */
        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->ccKeys as $ccKey) {
            if (isset($additionalData[$ccKey])) {
                $paymentInfo->setData(
                    $ccKey,
                    $additionalData[$ccKey]
                );
            }
        }
    }
}
