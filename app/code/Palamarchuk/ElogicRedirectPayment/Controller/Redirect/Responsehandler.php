<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Controller\Redirect;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\Serializer\Serialize;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\ResponseFieldsInterface;
use Palamarchuk\ElogicRedirectPayment\Model\Config;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Api\InvoiceRepositoryInterface;

class Responsehandler implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private JsonFactory                $jsonFactory,
        private RequestInterface           $request,
        private LoggerInterface            $logger,
        private OrderRepositoryInterface   $orderRepository,
        private BuilderInterface           $transactionBuilder,
        private CollectionFactory          $invoiceCollectionFactory,
        private InvoiceRepositoryInterface $invoiceRepository,
        private Config                     $config,
        private Serialize $serialize
    )
    {
    }

    public function execute(): ResultInterface
    {
        $response = $this->jsonFactory->create();
        $data = $this->request->getParam('data');
        $paymentData = $this->decodeData($data);

        if (!$this->checkSignature()) {
            $this->logger->error('Invalid signature. Payment data :' . json_encode($paymentData));
            $response->setData(['status' => 'error', 'code' => 400, 'errorMessage' => 'signature not correct']);

            return $response;
        }

        $order = $this->orderRepository->get($paymentData[ResponseFieldsInterface::ORDER_ID]);
        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData[ResponseFieldsInterface::PAYMENT_ID]);
            $payment->setTransactionId($paymentData[ResponseFieldsInterface::PAYMENT_ID]);
            $payment->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData]);

            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            $message = __('The captured amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->transactionBuilder;
            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData[ResponseFieldsInterface::PAYMENT_ID])
                ->setAdditionalInformation([\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData])
                ->setFailSafe(true)
                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
            $transactionId = $transaction->save()->getTransactionId();

            $invoice = $this->getInvoiceByOrder($order);
            $invoice->setTransactionId($transaction->getTxnId());
            $this->invoiceRepository->save($invoice);
            $payment->registerAuthorizationNotification($order->getGrandTotal());
            $payment->registerCaptureNotification($order->getGrandTotal());
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }

        $response->setData(['status' => 'ok', 'code' => 200, 'data' => $paymentData, 'order' => $order->getData()]);
        return $response;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    private function checkSignature(): bool
    {
        $publicKey = $this->config->getPublicKey();
        $privateKey = $this->config->getPrivateKey();
        $data = $this->request->getParam('data');
        $data = urldecode($data);
        $signature = $this->request->getParam('signature');
        $signature = urldecode($signature);
        $signatureCheck = base64_encode(sha1($privateKey . $data . $privateKey, true));

        return ($signatureCheck === $signature);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function decodeData(mixed $data): mixed
    {
        $data= urldecode($data);
        $jsonData = base64_decode($data);
        $rightJsonData = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $jsonData);
//        $this->serialize->unserialize($rightJsonData);

        return json_decode($rightJsonData, true, 512, JSON_THROW_ON_ERROR);
    }

    private function getInvoiceByOrder($order): InvoiceInterface
    {
        /** @var Collection $invoiceCollection */
        $invoiceCollection = $this->invoiceCollectionFactory->create();

        /** @var InvoiceInterface $invoice */
        $invoice = $invoiceCollection->setOrderFilter($order)->setPageSize(1)->getFirstItem();

        return $invoice;
    }
}
