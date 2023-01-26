<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\ApiUrlInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\RequestFieldsInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\VersionInterface;
use Palamarchuk\ElogicRedirectPayment\Model\Config;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private ResultFactory $resultFactory,
        private Session       $checkoutSession,
        private Config $config
    )
    {
    }

    public function execute(): ResultInterface
    {
        $publicKey = $this->config->getPublicKey();
        $privateKey =  $this->config->getPrivateKey();

        $order = $this->checkoutSession->getLastRealOrder();
        $data = array(
            RequestFieldsInterface::VERSION => VersionInterface::VERSION,
            RequestFieldsInterface::PUBLIC_KEY => $publicKey,
            RequestFieldsInterface::ACTION => 'pay',
            RequestFieldsInterface::AMOUNT => $order->getGrandTotal(),
            RequestFieldsInterface::PHONE => $order->getBillingAddress()->getTelephone(),
            RequestFieldsInterface::CURRENCY => $order->getOrderCurrency()->getCurrencyCode(),
            RequestFieldsInterface::DESCRIPTION => 'Order ' . $order->getId() . ' in magenapp',
            RequestFieldsInterface::ORDER_ID => $order->getId(),
            RequestFieldsInterface::RESULT_URL => $this->config->getResultUrl(),
            RequestFieldsInterface::SERVER_URL => $this->config->getServerUrl(),
        );

        $serializedData = json_encode($data);
        $encryptedData = base64_encode($serializedData);
        $signature = base64_encode(sha1($privateKey . $encryptedData . $privateKey, true));

        $uri = http_build_query([
            'data' => $encryptedData,
            'signature' => $signature,
        ]);
        $readyUrl = ApiUrlInterface::API_URL . '?' . $uri;

        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($readyUrl);

        return $redirect;
    }
}
