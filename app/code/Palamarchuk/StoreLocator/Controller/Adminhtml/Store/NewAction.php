<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\Controller\ResultInterface;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class NewAction extends Action
{
    private StoreLocationRepository $storeLocationRepository;

    public function __construct(
        Context                 $context,
        StoreLocationRepository $storeLocationRepository
    ) {
        parent::__construct($context);
        $this->storeLocationRepository = $storeLocationRepository;
    }

    public function execute(): ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Palamarchuk_StoreLocator::module');
        $resultPage->getConfig()->getTitle()->prepend('Stores');

        return $resultPage;
    }
}
