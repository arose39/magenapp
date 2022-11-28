<?php

namespace Palamarchuk\LuxuryTax\Model\InvoiceTotal;

use Magento\Sales\Model\Order\Total\AbstractTotal;

class LuxuryTaxTotal extends AbstractTotal
{
    public function collect(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        $order = $invoice->getOrder();
        $luxuryTaxAmount = $order->getLuxuryTaxAmount();
        $baseLuxuryTaxAmount = $order->getBaseLuxuryTaxAmount();
        $invoice->setLuxuryTaxAmount($luxuryTaxAmount);
        $invoice->setBaseLuxuryTaxAmount($baseLuxuryTaxAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $luxuryTaxAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseLuxuryTaxAmount);

        return $this;
    }
}
