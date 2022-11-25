<?php

namespace Palamarchuk\LuxuryTax\Model\InvoiceTotal;

use Magento\Sales\Model\Order\Total\AbstractTotal;

class LuxuryTaxTotal extends AbstractTotal
{
    public function collect(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        $order = $invoice->getOrder();
        $luxuryTaxAmount = $order->getLuxuryTaxAmount();
        $invoice->setLuxuryTaxAmount($luxuryTaxAmount);
        $invoice->setBaseLuxuryTaxAmount($luxuryTaxAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $luxuryTaxAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $luxuryTaxAmount);

        return $this;
    }
}
