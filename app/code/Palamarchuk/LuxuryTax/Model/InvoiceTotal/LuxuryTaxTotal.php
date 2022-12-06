<?php

namespace Palamarchuk\LuxuryTax\Model\InvoiceTotal;

use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Model\Order\Total\AbstractTotal;

class LuxuryTaxTotal extends AbstractTotal
{
    public function collect(InvoiceInterface $invoice)
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
