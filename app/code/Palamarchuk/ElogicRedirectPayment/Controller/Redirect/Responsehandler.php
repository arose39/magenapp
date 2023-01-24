<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Collection;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\DB\Transaction;

class Responsehandler implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private JsonFactory              $jsonFactory,
        private RequestInterface         $request,
        private LoggerInterface          $logger,
        private OrderRepositoryInterface $orderRepository,
        private BuilderInterface         $transactionBuilder,
        private CollectionFactory $invoiceCollectionFactory,
        private  InvoiceRepositoryInterface $invoiceRepository,

    )
    {
    }

    public function execute(): ResultInterface
    {



        $check = $this->checkSignature();

        $data = $this->request->getParam('data');
        $paymentData = $this->decodeData($data);
        $order = $this->orderRepository->get($paymentData['order_id']);


        try {
            $payment = $order->getPayment();
            $payment->setLastTransId($paymentData['payment_id']);
            $payment->setTransactionId($paymentData['payment_id']);
            $payment->setAdditionalInformation(
                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData]
            );

            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                $order->getGrandTotal()
            );
            $message = __('The captured amount is %1.', $formatedPrice);
            //get the object of builder class
            $trans = $this->transactionBuilder;

            $transaction = $trans->setPayment($payment)
                ->setOrder($order)
                ->setTransactionId($paymentData['payment_id'])
                ->setAdditionalInformation(
                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array)$paymentData]
                )
                ->setFailSafe(true)
                //build method creates the transaction and returns the object
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
            $err = $e->getMessage();
        }

        $response = $this->jsonFactory->create();
        $response->setData(['transaction_id' => $transactionId, 'check' => $check, 'data' => $paymentData, 'order' => $order->getData()]);

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
        $publicKey = 'sandbox_i73598312277';
        $privateKey = 'sandbox_w1l6ghAxhrYGbNRKfPpyodqfjkDFBJYod0wjhJS7';
        $data = $this->request->getParam('data');
        $signature = $this->request->getParam('signature');
        $signature = preg_replace('/%3D/', '=', $signature);
        $signatureCheck = base64_encode(sha1($privateKey . $data . $privateKey, true));

        return ($signatureCheck === $signature);
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    private function decodeData(mixed $data): mixed
    {
        $jsonData = base64_decode($data);
        $rightJsonData = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $jsonData);

        return json_decode($rightJsonData, true);
    }

    protected function getInvoiceByOrder($order):InvoiceInterface
    {
        /** @var Collection $invoiceCollection */
        $invoiceCollection = $this->invoiceCollectionFactory->create();

        /** @var InvoiceInterface $invoice */
        $invoice =  $invoiceCollection->setOrderFilter($order)->setPageSize(1)->getFirstItem();

        return $invoice;
    }
}
