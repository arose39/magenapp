<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Payment;

class InvoiceTransId
{
    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;

    /**
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function afterAccept(Payment $subject, Payment $result): Payment
    {
        $paymentMethod = $subject->getMethodInstance();
        if (!$paymentMethod instanceof \Palamarchuk\ElogicRedirectPayment\Model\RedirectPaymentMethod) {
            return $result;
        }

        $invoices = iterator_to_array($subject->getOrder()->getInvoiceCollection());
        $invoice = reset($invoices);
        if ($invoice) {
            $invoice->setTransactionId($subject->getLastTransId());
            $this->invoiceRepository->save($invoice);
        }

        return $result;
    }
}
