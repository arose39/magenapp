<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Controller\Adminhtml\Luxurytax;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\Model\View\Result\Page;

class Index extends Action
{
    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Palamarchuk_LuxuryTax::module');
        $resultPage->getConfig()->getTitle()->prepend('Luxury taxes');

        return $resultPage;
    }
}
