<?php

namespace Palamarchuk\LuxuryTax\Model\CreditMemoTotal;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class LuxuryTaxTotal extends AbstractTotal
{
    /**
     * Collect Creditmemo subtotal
     *
     * @param \Magento\Sales\Model\Order\Creditmemo $creditmemo
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $luxuryTaxRate = $order->getBaseLuxuryTaxAmount() / $order->getBaseSubtotal();
        $subtotal = 0;
        $baseSubtotal = 0;

        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->isDummy()) {
                continue;
            }

            $item->calcRowTotal();

            $subtotal += $item->getRowTotal();
            $baseSubtotal += $item->getBaseRowTotal();
        }

        $luxuryTaxAmount = $subtotal * $luxuryTaxRate;
        $baseLuxuryTaxAmount = $baseSubtotal * $luxuryTaxRate;
        $creditmemo->setTotalAmount('luxury_tax', $luxuryTaxAmount);
        $creditmemo->setBaseTotalAmount('luxury_tax', $baseLuxuryTaxAmount);
        $creditmemo->setLuxuryTaxAmount($luxuryTaxAmount);
        $creditmemo->setBaseLuxuryTaxAmount($baseLuxuryTaxAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $luxuryTaxAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseLuxuryTaxAmount);

        return $this;
    }
}
