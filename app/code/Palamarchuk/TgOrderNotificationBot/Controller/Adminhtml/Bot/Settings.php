<?php

declare(strict_types=1);

namespace Palamarchuk\TgOrderNotificationBot\Controller\Adminhtml\Bot;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\Manager;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Settings extends Action
{
    public function __construct(
        Context $context,
          private WriterInterface $configWriter,
        private Manager $cacheManager
    )
    {
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {

        if($this->getRequest()->getParams()!==[]){
            $request = $this->getRequest();
            $this->configWriter->save('tg_order_notification_bot/general/' . 'module_enabled', $request->getParam('enable'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'access_token', $request->getParam('token'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'hook', $request->getParam('hook'));
            $this->configWriter->save('tg_order_notification_bot/general/' . 'hook_is_set', 1);

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
