<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Controller\Bot;

use GuzzleHttp\Client;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements HttpGetActionInterface
{
    public function __construct(
        private ResultFactory $resultFactory,
        private Client        $client
    ) {
    }

    private $url = "https://api.telegram.org/bot5882390745:AAF-lmwKIXukQMjIZhk_DeZ7V_UttQDurH4/getUpdates";
    private $token = '5882390745:AAF-lmwKIXukQMjIZhk_DeZ7V_UttQDurH4';

    private $userId = 632875819;


    public function execute(): ResultInterface
    {

        // https://magenapp.requestcatcher.com/
        // https://api.telegram.org/bot5882390745:AAF-lmwKIXukQMjIZhk_DeZ7V_UttQDurH4/setWebhook?url=https://magenapp.requestcatcher.com


//        $data = file_get_contents('php://input');

        $data = '{"update_id":488972200,
"callback_query":{"id":"2718180947988877694","from":{"id":632875819,"is_bot":false,"first_name":"Andrew","last_name":"Rose","username":"ndrewrose","language_code":"ru"},"message":{"message_id":14,"from":{"id":5882390745,"is_bot":true,"first_name":"madjadja","username":"madjadja_bot"},"chat":{"id":632875819,"first_name":"Andrew","last_name":"Rose","username":"ndrewrose","type":"private"},"date":1676454628,"text":"asdasda  sdasdasd","entities":[{"offset":9,"length":8,"type":"bold"}],"reply_markup":{"inline_keyboard":[[{"text":"Button 1","callback_data":"test_2"},{"text":"Button 2","callback_data":"test_2"}]]}},"chat_instance":"2077862635677523939","data":"test_2"}}';
        $arrDataAnswer = json_decode($data, true);

        $urlPost = "https://api.telegram.org/bot" . $this->token . '/sendMessage';
        if($arrDataAnswer['callback_query']){
            $buttonData = $arrDataAnswer['callback_query']['data'];
            $chatId = $arrDataAnswer['callback_query']['message']['chat']['id'];
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => 'и тебе <b> BUTTON</b>'  . $buttonData,
                'parse_mode' => 'html']
            ];
            $r = $this->client->post($urlPost, $arrayQuery);
            $r = $r->getBody()->getContents();

            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result->setJsonData($r);

            return $result;

        }



        $textMessage = mb_strtolower($arrDataAnswer['message']['text']);
        $chatId = $arrDataAnswer['message']['chat']['id'];


        if ($textMessage === "привет") {
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => 'и тебе <b> ПРИВЕТ</b>',
                'parse_mode' => 'html']
            ];
            $r = $this->client->post($urlPost, $arrayQuery);
            $r = $r->getBody()->getContents();
        } else {
            $arrayQuery = ['form_params' => [
                'chat_id' => $chatId,
                'text' => 'не могу распознать команду',
                'parse_mode' => 'html']
            ];
            $r = $this->client->post($urlPost, $arrayQuery);
            $r = $r->getBody()->getContents();
        }


//        $text = "Текстовое сообщение";
//        $text = urlencode($text);
//        $urlquery = "https://api.telegram.org/bot" . $this->token . '/sendMessage?chat_id=' . $this->userId . '&text=' . $text;
//        $urlPost = "https://api.telegram.org/bot" . $this->token . '/sendMessage';
//
//        $arrayQuery = ['form_params' => [
//            'chat_id' => $this->userId,
//            'text' => 'asdasda <b> sdasdasd</b>',
//            'parse_mode' => 'html',
//            'reply_markup' => json_encode(array(
//                'inline_keyboard' => array(
//                    array(
//                        array(
//                            'text' => 'Button 1',
//                            'callback_data' => 'test_2',
//                        ),
//
//                        array(
//                            'text' => 'Button 2',
//                            'callback_data' => 'test_2',
//                        ),
//                    )
//                ),
//            ), JSON_THROW_ON_ERROR),
//        ]];
//
        ////        $r = $this->client->get($urlquery);
//
//        $r = $this->client->post($urlPost, $data);
//        $r = $r->getBody()->getContents();

        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result->setJsonData($r);

        return $result;
    }
}
