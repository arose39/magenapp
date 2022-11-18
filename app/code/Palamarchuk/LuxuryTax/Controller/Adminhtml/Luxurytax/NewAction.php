<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Controller\Adminhtml\Luxurytax;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends Action
{
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Palamarchuk_LuxuryTax::module');
        $resultPage->getConfig()->getTitle()->prepend('Stores');

        return $resultPage;
    }
}
