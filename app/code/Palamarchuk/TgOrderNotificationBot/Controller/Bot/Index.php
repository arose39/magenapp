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
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Palamarchuk\TgOrderNotificationBot\Model\ModuleConfig;

class Index implements HttpPostActionInterface, CsrfAwareActionInterface
{
    const API_URL = "https://api.telegram.org";
    const API_METHOD = "sendMessage";

    public function __construct(
        private ResultFactory          $resultFactory,
        private Client                 $client,
        private OrderResourceInterface $orderResource,
        private OrderInterfaceFactory  $orderFactory,
        private ModuleConfig $config
    )
    {
    }

    public function execute(): ResultInterface
    {
        $data = file_get_contents('php://input');
        $arrDataAnswer = json_decode($data, true);
        $urlPost = self::API_URL . "/bot" . $this->config->getAccessToken() . "/". self::API_METHOD;

        $arrayQuery = $this->getResponseArrayQuery($arrDataAnswer);
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

    private function getOrderByIncrementId(string $incrementId)
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId, OrderInterface::INCREMENT_ID);

        return $order;
    }

    private function getResponseArrayQuery(array $arrDataAnswer): array
    {
        // Replies for button requests
        if (isset($arrDataAnswer['callback_query'])) {
            $buttonData = $arrDataAnswer['callback_query']['data'];
            $chatId = $arrDataAnswer['callback_query']['message']['chat']['id'];
            /** @var OrderInterface $order */
            $order = $this->getOrderByIncrementId($buttonData);
            if ($order->getEntityId()) {
                $arrayQuery = ['form_params' => [
                    'chat_id' => $chatId,
                    'text' => "Currently, your order is in <b>" . strtoupper($order->getStatus()) . "</b> status",
                    'parse_mode' => 'html',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Перевірити знову',
                                    'callback_data' => $buttonData,
                                )
                            )
                        ),
                    ], JSON_THROW_ON_ERROR),
                ]];

                return $arrayQuery;
            }
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => "Не можу знайти замовлення за кодом $buttonData",
                'parse_mode' => 'html']
            ];

            return $arrayQuery;
        }

        // Replies for text messages requests
        $textMessage = mb_strtolower($arrDataAnswer['message']['text']);
        $chatId = $arrDataAnswer['message']['chat']['id'];
        if ($textMessage === "/start") {
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => 'Привіт! Цей чат допомогає відстежувати статус вешого замовлення.
            Для перевірки статуса замовлення, просто напишіть номер замовлення
            (9 цифр, наприклад <b>000001123</b>)',
                'parse_mode' => 'html']
            ];

            return $arrayQuery;
        }

        if (!(preg_match('/^\d{9}$/', $textMessage))) {
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => 'Не можу розібрати вашого запиту.
            Цей чат допомогає відстежувати статус вешого замовлення.
            Для перевірки статуса замовлення, просто напишіть номер замовлення
            (9 цифр, наприклад <b>000001123</b>)',
                'parse_mode' => 'html']
            ];

            return $arrayQuery;
        } else {
            /** @var OrderInterface $order */
            $order = $this->getOrderByIncrementId($textMessage);
            if ($order->getEntityId()) {
                $arrayQuery = ['form_params' => [
                    'chat_id' => $chatId,
                    'text' => "Currently, your order is in <b>" . strtoupper($order->getStatus()) . "</b> status",
                    'parse_mode' => 'html',
                    'reply_markup' => json_encode([
                        'inline_keyboard' => array(
                            array(
                                array(
                                    'text' => 'Перевірити знову',
                                    'callback_data' => $textMessage,
                                )
                            )
                        ),
                    ], JSON_THROW_ON_ERROR),
                ]];

                return $arrayQuery;
            }

            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => "Не можу знайти замовлення за кодом $textMessage",
                'parse_mode' => 'html']
            ];

            return $arrayQuery;
        }
    }
}
