<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Model;

use GuzzleHttp\Client;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;

class ProcessTgBotQueueMsg
{
    const API_URL = "https://api.telegram.org";
    const API_METHOD = "sendMessage";

    public function __construct(
        private Client                $client,
        private TgChatResponseCreator $chatResponseCreator,
        private ModuleConfig          $config
    )
    {
    }

    public function process(EnvelopeInterface $message)
    {
        $message = $message->getBody();
        $message = $this->replaceEscapers($message);
        $arrDataAnswer = json_decode($message, true);
        $urlPost = self::API_URL . "/bot" . $this->config->getAccessToken() . "/" . self::API_METHOD;
        $arrayQuery = $this->chatResponseCreator->getResponseArrayQuery($arrDataAnswer);
        $r = $this->client->post($urlPost, $arrayQuery);
        $r = $r->getBody()->getContents();
        $decodedResult = json_decode($r, true);
        if ($decodedResult['ok']) {
            return true;
        }

        return false;
    }

    private function replaceEscapers(string $message): string
    {
        return trim(str_replace('\"', '"', $message), '"');
    }
}
