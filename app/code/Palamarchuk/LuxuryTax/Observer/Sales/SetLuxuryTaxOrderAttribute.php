<?php

namespace Palamarchuk\LuxuryTax\Observer\Sales;

use Magento\Framework\Event\Observer;

class SetLuxuryTaxOrderAttribute implements \Magento\Framework\Event\ObserverInterface
{
//    public function __construct(
//        \Psr\Log\LoggerInterface $logger
//    ) {
//        $this->logger = $logger;
//    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getData('order');
        $order->setLuxuryTaxAmount(47.47);
        $order->save();
    }
}
