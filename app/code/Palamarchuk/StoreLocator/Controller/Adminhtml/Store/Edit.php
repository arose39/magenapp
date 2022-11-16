<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class Edit extends Action
{
    private $storeLocationRepository;

    public function __construct(
        Context                 $context,
        StoreLocationRepository $storeLocationRepository
    )
    {
        parent::__construct($context);
        $this->storeLocationRepository = $storeLocationRepository;
    }

    public function execute(): ResultInterface
    {
        $id = (int) $this->getRequest()->getParam('id');
        try {
            $storeLocation = $this->storeLocationRepository->get($id);

            /** @var Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $resultPage->setActiveMenu('Palamarchuk_StoreLocator::module');
            $resultPage->getConfig()
                ->getTitle()
                ->prepend(__('Edit store location: %store', ['store' => $storeLocation->getName()]));
        } catch (NoSuchEntityException $e) {
            /** @var Redirect $result */
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(
                __('StoreLocation with id "%value" does not exist.', ['value' => $id])
            );
            $result->setPath('*/*');
        }

        return $resultPage;
    }
}
