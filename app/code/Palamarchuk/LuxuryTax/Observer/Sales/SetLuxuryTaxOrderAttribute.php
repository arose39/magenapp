<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Observer\Sales;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

class SetLuxuryTaxOrderAttribute implements ObserverInterface
{
    public const NOT_LOGGED_IN_CUSTOMER_GROUP = 0;

    public function __construct(
        private GroupRepositoryInterface $groupRepository,
        private LuxuryTaxRepository      $luxuryTaxRepository,
    )
    {
    }

    public function execute(Observer $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $orderSubtotal = $order->getSubtotal();
        $orderBaseSubtotal = $order->getBaseSubtotal();
        if ($order->getCustomerIsGuest()){
            $customerGroupId = self::NOT_LOGGED_IN_CUSTOMER_GROUP;
        }else{
            $customerGroupId = $order->getCustomerGroupId();
        }
        $luxuryTaxId = (int)$this->groupRepository->getById($customerGroupId)->getExtensionAttributes()->getLuxuryTaxId();
        //getLuxuryTaxAmount -> getLuxuryTaxRate . Wrong naming in luxuryTaxEntity
        $luxuryTaxPercent = $this->luxuryTaxRepository->get($luxuryTaxId)->getLuxuryTaxAmount() / 100;
        $luxuryTaxAmount = $orderSubtotal * $luxuryTaxPercent;
        $luxuryTaxAmount = number_format($luxuryTaxAmount, 2, '.');
        $baseLuxuryTaxAmount = $orderBaseSubtotal * $luxuryTaxPercent;
        $baseLuxuryTaxAmount = number_format($baseLuxuryTaxAmount, 2, '.');

        $order->setLuxuryTaxAmount($luxuryTaxAmount);
        $order->setBaseLuxuryTaxAmount($baseLuxuryTaxAmount);
        $order->save();
    }
}
