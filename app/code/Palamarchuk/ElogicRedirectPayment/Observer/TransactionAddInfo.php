<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Data\PaymentMethodCodeInterface;

class TransactionAddInfo implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getOrder()->getPayment()->getMethod() === PaymentMethodCodeInterface::CODE) {
            $refundInfo = $creditmemo->getOrder()->getPayment()->getAdditionalInformation()['raw_details_info'];
            $creditmemo->getOrder()->getPayment()->getCreatedTransaction()->setAdditionalInformation('raw_details_info', $refundInfo);
        }
    }
}
