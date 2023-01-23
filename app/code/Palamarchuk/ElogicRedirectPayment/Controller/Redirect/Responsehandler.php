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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Framework\DB\Transaction;

class Responsehandler implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private ResultFactory            $resultFactory,
        private JsonFactory              $jsonFactory,
        private RequestInterface         $request,
        private LoggerInterface          $logger,
        private OrderRepositoryInterface $orderRepository,
        private BuilderInterface         $transactionBuilder,
        private InvoiceService $invoiceService,
    )
    {
    }

    public function execute(): ResultInterface
    {


//$a = 'signature=hQME9PwCv%2B3Wv%2Badfkjvdq3LfpY%3D&data=eyJwYXltZW50X2lkIjoyMTk4MjMxNjAyLCJhY3Rpb24iOiJwYXkiLCJzdGF0dXMiOiJzdWNjZXNzIiwidmVyc2lvbiI6MywidHlwZSI6ImJ1eSIsInBheXR5cGUiOiJjYXJkIiwicHVibGljX2tleSI6InNhbmRib3hfaTczNTk4MzEyMjc3IiwiYWNxX2lkIjo0MTQ5NjMsIm9yZGVyX2lkIjoiNTYiLCJsaXFwYXlfb3JkZXJfaWQiOiJHUzBQUkYxWTE2NzQ0Njk5NDg3OTI5MzQiLCJkZXNjcmlwdGlvbiI6Ik9yZGVyIDU2IGluIG1hZ2VuYXBwIiwic2VuZGVyX2NhcmRfbWFzazIiOiI0MjQyNDIqNDIiLCJzZW5kZXJfY2FyZF9iYW5rIjoiVGVzdCIsInNlbmRlcl9jYXJkX3R5cGUiOiJ2aXNhIiwic2VuZGVyX2NhcmRfY291bnRyeSI6ODA0LCJpcCI6IjkzLjE3NS4yMDAuMTMwIiwiYW1vdW50Ijo0MS44MSwiY3VycmVuY3kiOiJVU0QiLCJzZW5kZXJfY29tbWlzc2lvbiI6MC4wLCJyZWNlaXZlcl9jb21taXNzaW9uIjowLjYzLCJhZ2VudF9jb21taXNzaW9uIjowLjAsImFtb3VudF9kZWJpdCI6MTU2NS45MiwiYW1vdW50X2NyZWRpdCI6MTU2NS45MiwiY29tbWlzc2lvbl9kZWJpdCI6MC4wLCJjb21taXNzaW9uX2NyZWRpdCI6MjMuNDksImN1cnJlbmN5X2RlYml0IjoiVUFIIiwiY3VycmVuY3lfY3JlZGl0IjoiVUFIIiwic2VuZGVyX2JvbnVzIjowLjAsImFtb3VudF9ib251cyI6MC4wLCJtcGlfZWNpIjoiNyIsImlzXzNkcyI6ZmFsc2UsImxhbmd1YWdlIjoidWsiLCJjcmVhdGVfZGF0ZSI6MTY3NDQ2OTk0ODc5NywiZW5kX2RhdGUiOjE2NzQ0Njk5NDg5MDksInRyYW5zYWN0aW9uX2lkIjoyMTk4MjMxNjAyfQ%3D%3D';

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

//            $invoice = $this->invoiceService->prepareInvoice($order);
//            $invoice->setRequestedCaptureCase(Invoice::CAPTURE_ONLINE);
//            $invoice->setState(Invoice::STATE_PAID);
//            $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
//            $invoice->register();
//            $invoice->getOrder()->setIsInProcess(true);
//            $invoice->pay();
//            $invoice->save();
//            $order->setTotalPaid($order->getGrandTotal());
//            $order->setBaseTotalPaid($order->getBaseGrandTotal());

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
//            $invoice = $payment->getCreatedInvoice();
//            $invoice = $payment->getCreatedInvoice();


            $payment->save();

            $order->save();
            $transactionId = $transaction->save()->getTransactionId();
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
}
