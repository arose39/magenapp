<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Controller\Adminhtml\Bot;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use GuzzleHttp\Client;

class Settings extends Action
{
    public function __construct(
        Context                 $context,
        private WriterInterface $configWriter,
        private Manager         $cacheManager,
        private Client          $client
    )
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {


        // https://api.telegram.org/bot5882390745:AAF-lmwKIXukQMjIZhk_DeZ7V_UttQDurH4/setWebhook?url=https://magenapp.requestcatcher.com
//            "description": "Webhook was set"
//            "Webhook is already set"

        if ($this->getRequest()->getParams() !== []) {
            $request = $this->getRequest();
            $this->configWriter->save('tg_order_notification_bot/general/' . 'module_enabled', $request->getParam('enable'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'access_token', $request->getParam('token'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'hook', $request->getParam('hook'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'hook_is_set', 1);
            try {
                $this->client->get("https://api.telegram.org/bot" . $request->getParam('token') . "/setWebhook?url=" . $request->getParam('hook'));
            }catch (\Exception $e){
                $this->messageManager->addErrorMessage("Something went wrong wile setting hook. Please check hook url and token");
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->configWriter->save('tg_order_notification_bot/general/' . 'hook', '');
                $this->configWriter->save('tg_order_notification_bot/general/' . 'hook_is_set', 0);
            }

            $this->cacheManager->clean(['config']);
            $this->messageManager->addSuccessMessage("New configuration was saved. Refresh page to see changes");
        };


        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Palamarchuk_TgOrderNotificationBot::module');
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(__('Edit settings telegram order notification bot'));

        return $resultPage;
    }
}
