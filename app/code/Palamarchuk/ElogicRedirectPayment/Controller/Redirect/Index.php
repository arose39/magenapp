<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private ResultFactory $resultFactory,
        private Session       $checkoutSession,
    )
    {
    }

    public function execute(): ResultInterface
    {
        $publicKey = 'sandbox_i73598312277';
        $privateKey = 'sandbox_w1l6ghAxhrYGbNRKfPpyodqfjkDFBJYod0wjhJS7';


        $urlCheckoutLiqPay = "https://www.liqpay.ua/api/3/checkout";
        $order = $this->checkoutSession->getLastRealOrder();
        $data = array(
            'version' => '3',
            'public_key' => $publicKey,
            'action' => 'pay',
            'amount' => $order->getGrandTotal(),
            'phone'=> $order->getBillingAddress()->getTelephone(),
            'currency' => $order->getOrderCurrency()->getCurrencyCode(),
            'description' => 'Order ' . $order->getId() . ' in magenapp',
            'order_id' => $order->getId(),
            "result_url"=>"https://magenapp.dev.com/redirect_payment/redirect/responsehandler",
            "server_url"=>"https://magenapp.requestcatcher.com/");

        $serializedData = json_encode($data);
        $encryptedData = base64_encode($serializedData);
        $signature = base64_encode(sha1($privateKey . $encryptedData . $privateKey, true));

        $uri = http_build_query([
            'data' => $encryptedData,
            'signature' => $signature,
        ]);


        $readyUrl = $urlCheckoutLiqPay . '?' . $uri;


        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($readyUrl);

        return $redirect;
    }
}
