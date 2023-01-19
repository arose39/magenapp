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
        $data = array(
            'version' => '3',
            'public_key' => $publicKey,
            'action' => 'pay',
            'amount' => '10',
            'phone'=> "380633599878",
            'currency' => 'USD',
            'description' => 'description redirect for Magento 2',
            'order_id' => '000000013a',
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

//        $order = $this->checkoutSession->getLastRealOrder()->getData();
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($readyUrl);

        return $redirect;
    }
}
