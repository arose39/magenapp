<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Controller\Redirect;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class Responsehandler implements HttpGetActionInterface
{
    public function __construct(
        private ResultFactory    $resultFactory,
        private RequestInterface $request,
        private LoggerInterface  $logger
    )
    {
    }

    public function execute(): ResultInterface
    {

        $answer = 'signature=TzZjeXTunu7HguE6rMyf6UtpVuE%3D
        &data=eyJwYXltZW50X2lkIjoyMTk1MDE3NTI5LCJhY3Rpb24iOiJwYXkiLCJzdGF0dXMiOiJzdWNjZXNzIiwidmVyc2lvbiI6MywidHlwZSI6ImJ1eSIsInBheXR5cGUiOiJjYXJkIiwicHVibGljX2tleSI6InNhbmRib3hfaTczNTk4MzEyMjc3IiwiYWNxX2lkIjo0MTQ5NjMsIm9yZGVyX2lkIjoiMDAwMDAwMDEzYSIsImxpcXBheV9vcmRlcl9pZCI6IllLU003NzBYMTY3NDE0MTg0NDI5MTQ4OCIsImRlc2NyaXB0aW9uIjoiZGVzY3JpcHRpb24gcmVkaXJlY3QgZm9yIE1hZ2VudG8gMiIsInNlbmRlcl9jYXJkX21hc2syIjoiNDI0MjQyKjQyIiwic2VuZGVyX2NhcmRfYmFuayI6IlRlc3QiLCJzZW5kZXJfY2FyZF90eXBlIjoidmlzYSIsInNlbmRlcl9jYXJkX2NvdW50cnkiOjgwNCwiaXAiOiI5My4xNzUuMjAxLjIwNCIsImFtb3VudCI6MTAuMCwiY3VycmVuY3kiOiJVU0QiLCJzZW5kZXJfY29tbWlzc2lvbiI6MC4wLCJyZWNlaXZlcl9jb21taXNzaW9uIjowLjE1LCJhZ2VudF9jb21taXNzaW9uIjowLjAsImFtb3VudF9kZWJpdCI6Mzc0LjUzLCJhbW91bnRfY3JlZGl0IjozNzQuNTMsImNvbW1pc3Npb25fZGViaXQiOjAuMCwiY29tbWlzc2lvbl9jcmVkaXQiOjUuNjIsImN1cnJlbmN5X2RlYml0IjoiVUFIIiwiY3VycmVuY3lfY3JlZGl0IjoiVUFIIiwic2VuZGVyX2JvbnVzIjowLjAsImFtb3VudF9ib251cyI6MC4wLCJtcGlfZWNpIjoiNyIsImlzXzNkcyI6ZmFsc2UsImxhbmd1YWdlIjoidWsiLCJjcmVhdGVfZGF0ZSI6MTY3NDE0MTg0NDI5OCwiZW5kX2RhdGUiOjE2NzQxNDE4NDQ0MjYsInRyYW5zYWN0aW9uX2lkIjoyMTk1MDE3NTI5fQ%3D%3D';

        $requestString = json_encode($this->request->getParams());
//        $this->logger->debug($requestString);
        $readyUrl = 'https://magenapp.dev.com/checkout/onepage/success/';
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $redirect->setUrl($readyUrl);

        return $redirect;
    }
}
