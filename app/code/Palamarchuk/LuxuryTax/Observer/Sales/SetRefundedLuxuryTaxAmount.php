<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class SetRefundedLuxuryTaxAmount implements ObserverInterface
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var CreditmemoInterface $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        /** @var OrderInterface $order */
        $order = $creditmemo->getOrder();

        $luxuryTaxAmount = $creditmemo->getLuxuryTaxAmount();
        $baseLuxuryTaxAmount = $creditmemo->getBaseLuxuryTaxAmount();
        $luxuryTaxAmount = number_format($luxuryTaxAmount, 2, '.');
        $baseLuxuryTaxAmount = number_format($baseLuxuryTaxAmount, 2, '.');

        $order->setLuxuryTaxRefunded($luxuryTaxAmount);
        $order->setBaseLuxuryTaxRefunded($baseLuxuryTaxAmount);
        $this->orderRepository->save($order);
    }
}
