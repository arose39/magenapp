<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Controller\Bot;

use GuzzleHttp\Client;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Palamarchuk\TgOrderNotificationBot\Model\ModuleConfig;
use Palamarchuk\TgOrderNotificationBot\Model\TgChatResponseCreator;

class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    const API_URL = "https://api.telegram.org";
    const API_METHOD = "sendMessage";

    public function __construct(
        private ResultFactory          $resultFactory,
        private Client                 $client,
        private TgChatResponseCreator $chatResponseCreator,
        private ModuleConfig $config
    )
    {
    }

    public function execute(): ResultInterface
    {
        $data = file_get_contents('php://input');
        $arrDataAnswer = json_decode($data, true);
        $urlPost = self::API_URL . "/bot" . $this->config->getAccessToken() . "/". self::API_METHOD;

        $arrayQuery = $this->chatResponseCreator->getResponseArrayQuery($arrDataAnswer);
        $r = $this->client->post($urlPost, $arrayQuery);
        $r = $r->getBody()->getContents();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setJsonData($r);

        return $result;
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
