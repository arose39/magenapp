<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Api\Data\OrderInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\ApiUrlInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\RequestFieldsInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\VersionInterface;
use Palamarchuk\ElogicRedirectPayment\Model\Config;

class Refund
{
    const REQUEST_TIMEOUT = 30;
    const REFUND_URL = 'https://www.liqpay.ua/api/request';
    const REFUND_ACTION = 'refund';

    public function __construct(
        private ZendClientFactory  $clientFactory,
        private TransferBuilder $transferBuilder,
        private Config $config,
        private  Logger $logger
    ) {
    }

    /**
     * @throws LocalizedException
     * @throws \JsonException
     */
    public function refund(OrderInterface $order)
    {
        $data = array(
            RequestFieldsInterface::VERSION => VersionInterface::VERSION,
            RequestFieldsInterface::PUBLIC_KEY => $this->config->getPublicKey(),
            RequestFieldsInterface::ACTION => self::REFUND_ACTION,
            RequestFieldsInterface::ORDER_ID => $order->getId(),
        );
        $encryptedData = $this->getEncryptedData($data);
        $signature = $this->getSignature($encryptedData);
        $uri = http_build_query([
            'data' => $encryptedData,
            'signature' => $signature,
        ]);
        $transferObject = $this->getTransferObject($uri);
        $client = $this->buildClient($transferObject);
        $response = $client->request();
        if (json_decode($response->getRawBody(), true, 512, JSON_THROW_ON_ERROR)['status'] === 'error') {
            $this->logger->debug([$response->getRawBody()]);
            throw new \Magento\Framework\Exception\LocalizedException(__($response->getRawBody()));
        }

        return $response->getRawBody();
    }

    private function buildClient(TransferInterface $transferObject): mixed
    {
        $client = $this->clientFactory->create();
        $client->setConfig($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());
        $client->setRawData($transferObject->getBody());
        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody($transferObject->shouldEncode());
        $client->setUri($transferObject->getUri());

        return $client;
    }

    private function getEncryptedData(array $data): string
    {
        $serializedData = json_encode($data);

        return base64_encode($serializedData);
    }

    private function getSignature(string $encryptedData): string
    {
        return base64_encode(sha1($this->config->getPrivateKey() . $encryptedData . $this->config->getPrivateKey(), true));
    }

    private function getTransferObject(string $uri): \Magento\Payment\Gateway\Http\Transfer|TransferInterface
    {
        return $this->transferBuilder
            ->setUri(self::REFUND_URL)
            ->setMethod('POST')
            ->setClientConfig([
                'timeout' => self::REQUEST_TIMEOUT,
                'verifypeer' => true
            ])
            ->setBody($uri)
            ->setHeaders([])
            ->build();
    }
}
