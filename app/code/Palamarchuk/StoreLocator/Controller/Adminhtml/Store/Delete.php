<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\StoreLocator\Model\ImageUploader;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class Delete extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    private StoreLocationRepository $storeLocationRepository;

    public function __construct(
        Context $context,
        StoreLocationRepository $storeLocationRepository,
    ) {
        parent::__construct($context);
        $this->storeLocationRepository = $storeLocationRepository;
    }

    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeLocationId = (int)$this->getRequest()->getParam('id');

        if (!$storeLocationId) {
            $this->messageManager->addErrorMessage(__('Error.'));

            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $storeLocation = $this->storeLocationRepository->get($storeLocationId);
            $this->storeLocationRepository->delete($storeLocation);
            $this->messageManager->addSuccessMessage(__('You deleted the store location'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Cannot delete store location'));
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
