<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Http;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Client
 */
class Client implements ClientInterface
{
    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Client constructor.
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param ConverterInterface|null $converter
     */
    public function __construct(
        ZendClientFactory  $clientFactory,
        Logger             $logger,
        ConverterInterface $converter = null
    )
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
        $this->converter = $converter;
    }

    /**
     * @param TransferInterface $transferObject
     * @return array
     * @throws ClientException
     * @throws ConverterException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request_uri' => $transferObject->getUri(),
            'request' => $transferObject->getBody()
        ];

        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $result = [];
//        try {
        $client->setConfig($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());
        $client->setRawData($transferObject->getBody());
        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody($transferObject->shouldEncode());
        $client->setUri($transferObject->getUri());

//        $response = $client->request();
//
//            $result = $this->converter
//                ? $this->converter->convert($response->getBody())
//                : [$response->getBody()];
//
//            $log['response'] = $result;
//        } catch (\Zend_Http_Client_Exception $exception) {
//            throw new ClientException(__($exception->getMessage()));
//        } catch (ConverterException $exception) {
//            throw $exception;
//        } finally {
//            $this->logger->debug($log);
//        }
        $result = [
            'acq_id' => 'tralala',
            'action' => 'tralala',
            'payment_id' => 'tralala',
            'version' => 'tralala',
            'paytype' => 'tralala',
            'order_id' => 'tralala',
            'liqpay_order_id' => 'tralala',
            'public_key' => 'tralala',
            'card_token' => 'tralala',
            'transaction_id' => '123123123123',
            'create_date' => 'tralala',
            'end_date' => 'tralala',
            'sender_card_mask2' => 'tralala',
            'sender_card_bank' => 'tralala',
            'sender_card_type' => 'tralala',
            'status' => 'tralala',
            ];

        return $result;
    }
}
