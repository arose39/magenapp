<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicIframePayment\Controller\Info;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\Quote;

class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    public function __construct(
        private JsonFactory $jsonFactory,
        private Session       $checkoutSession,
        private Quote $quote,
        private RequestInterface         $request,
    )
    {
    }

    public function execute(): ResultInterface
    {
        $publicKey = 'sandbox_i73598312277';
        $privateKey = 'sandbox_w1l6ghAxhrYGbNRKfPpyodqfjkDFBJYod0wjhJS7';


//        $requestData = $this->request->getParams();
//        $reservedOrderIncrementId = $this->quote->reserveOrderId()->getReservedOrderId();
        $reservedOrderIncrementId ='00000115';
        $urlCheckoutLiqPay = "https://www.liqpay.ua/api/3/checkout";
        $order = $this->checkoutSession->getLastRealOrder();
        $data = array(
            'action'         => 'pay',
            'amount'         => $this->request->getParam('amount'),
            'currency'       => $this->request->getParam('currency'),
            'description'    => 'description  for IFRAME',
            'order_id'       => $reservedOrderIncrementId,
            'version'        => '3'
          );

        $serializedData = json_encode($data);
        $encryptedData = base64_encode($serializedData);
        $signature = base64_encode(sha1($privateKey . $encryptedData . $privateKey, true));



        $response = $this->jsonFactory->create();
        $response->setData(['data' => $encryptedData, 'signature' => $signature, 'reserved_order_id' => $reservedOrderIncrementId]);
//        $response->setData(['data'=>'hello world', 'signature' => 'asdasfdaser23ra3edca3dcad3c', 'reserved_order_id' => $reservedOrderIncrementId]);

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
}
