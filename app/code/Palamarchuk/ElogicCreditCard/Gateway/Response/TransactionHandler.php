<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Response;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment;
use Palamarchuk\ElogicCreditCard\Api\Sdk\ResponseFieldsInterface;

/**
 * Class TransactionHandler
 */
class TransactionHandler implements HandlerInterface
{
    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var InfoInterface|Payment $payment */
        $payment = $handlingSubject['payment']->getPayment();

        $transactionId = $response[ResponseFieldsInterface::TRANSACTION_ID];
        $rawDetails = [];
        $payment->setCcTransId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);

        foreach ($response as $key => $value) {
            $payment->setTransactionAdditionalInfo($key, $value);
            $rawDetails[$key] = $value;
        }

        $payment->setTransactionAdditionalInfo('raw_details_info', $rawDetails);
    }
}
