<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Controller\Adminhtml\PublishMessage;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class TgBotPublish implements HttpPostActionInterface, CsrfAwareActionInterface
{
    private JsonFactory $resultJsonFactory;
    private PublisherInterface $publisher;
    private LoggerInterface $logger;

    public function __construct(
        JsonFactory        $resultJsonFactory,
        PublisherInterface $publisher, // use for publish message in RabbitMQ
        LoggerInterface    $logger
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $resultJson = $this->resultJsonFactory->create();
            $data = file_get_contents('php://input');

            //decode and again decode for correct format data
            $arrDataAnswer = json_decode($data, true);
            $encodedData = json_encode($arrDataAnswer);
            $this->publisher->publish('tgbot.topic', $encodedData);
            $result = ['status' => 'ok', 'msg' => 'success', 'code' => 200];
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage()];
            return $resultJson->setData($result);
        }
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
