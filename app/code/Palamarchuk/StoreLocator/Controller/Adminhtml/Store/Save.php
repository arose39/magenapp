<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Controller\Adminhtml\Store;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Palamarchuk\StoreLocator\Model\ImageUploader;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationFactory;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;
use Palamarchuk\StoreLocator\Model\WorkScheduleFactory;
use Palamarchuk\StoreLocator\Model\WorkScheduleRepository;

class Save extends Action implements HttpPostActionInterface
{
    private StoreLocationRepository $storeLocationRepository;
    private StoreLocationFactory $storeLocationFactory;
    private ImageUploader $imageUploader;
    private WorkScheduleRepository $workScheduleRepository;
    private WorkScheduleFactory $workScheduleFactory;

    public function __construct(
        Context                 $context,
        StoreLocationRepository $storeLocationRepository,
        WorkScheduleRepository  $workScheduleRepository,
        StoreLocationFactory    $storeLocationFactory,
        WorkScheduleFactory     $workScheduleFactory,
        ImageUploader           $imageUploader
    )
    {
        parent::__construct($context);
        $this->storeLocationRepository = $storeLocationRepository;
        $this->workScheduleRepository = $workScheduleRepository;
        $this->storeLocationFactory = $storeLocationFactory;
        $this->workScheduleFactory = $workScheduleFactory;
        $this->imageUploader = $imageUploader;
    }

    /**
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $requestData = $request->getPostValue();
        if (!$request->isPost() || empty($requestData['general'])) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $resultRedirect->setPath('*/*/new');
            return $resultRedirect;
        }
        try {
            $id = (int)$requestData['general'][StoreLocation::ID];
            $storeLocation = $this->storeLocationRepository->get($id);
        } catch (\Exception $e) {
            $storeLocation = $this->storeLocationFactory->create();
        }
        $storeLocation->setName($requestData['general']['name']);
        $storeLocation->setStoreUrlKey($requestData['general']['store_url_key']);
        $storeLocation->setDescription($requestData['general']['description']);
        $storeLocation->setAddress($requestData['general']['address']);
        $storeLocation->setCity($requestData['general']['city']);
        $storeLocation->setCountry($requestData['general']['country']);
        $storeLocation->setState((int)$requestData['general']['state']);
        $storeLocation->setZip($requestData['general']['zip']);
        $storeLocation->setPhone($requestData['general']['phone']);
        $storeLocation->setLatitude((float)$requestData['general']['latitude']);
        $storeLocation->setLongitude((float)$requestData['general']['longitude']);

        if (isset($requestData['general']['store_img'])) {
            $imageName = $requestData['general']['store_img'][0]['name'];
            $this->imageUploader->moveImageFromTmp($imageName);
            if ($storeLocation->getStoreImg()) {
                $this->imageUploader->deleteImage($storeLocation->getStoreImg());
            }
            $storeLocation->setStoreImg($imageName);
        }

        $workSchedule = $storeLocation->getWorkSchedule() ?? $this->workScheduleFactory->create();
        $workSchedule->setMonday($requestData['work_schedule']['monday_from'] . " - " . $requestData['work_schedule']['monday_to']);
        $workSchedule->setTuesday($requestData['work_schedule']['tuesday_from'] . " - " . $requestData['work_schedule']['tuesday_to']);
        $workSchedule->setWednesday($requestData['work_schedule']['wednesday_from'] . " - " . $requestData['work_schedule']['wednesday_to']);
        $workSchedule->setThursday($requestData['work_schedule']['thursday_from'] . " - " . $requestData['work_schedule']['thursday_to']);
        $workSchedule->setFriday($requestData['work_schedule']['friday_from'] . " - " . $requestData['work_schedule']['friday_to']);
        $workSchedule->setSaturday($requestData['work_schedule']['saturday_from'] . " - " . $requestData['work_schedule']['saturday_to']);
        $workSchedule->setSunday($requestData['work_schedule']['sunday_from'] . " - " . $requestData['work_schedule']['sunday_to']);

        try {
            $storeLocation = $this->storeLocationRepository->save($storeLocation);
            $workSchedule->setStoreLocationId($storeLocation->getId());
            $this->workScheduleRepository->save($workSchedule);
            $this->imageUploader->deleteTmpImages();
            $this->processRedirectAfterSuccessSave($resultRedirect, $storeLocation->getId());
            $this->messageManager->addSuccessMessage(__('Store location and it work schedul were saved.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
            $resultRedirect->setPath('*/*/new');
        }

        return $resultRedirect;
    }

    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, string $id): void
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    'id' => $id,
                    '_current' => true,
                ]
            );
        } elseif ($this->getRequest()->getParam('redirect_to_new')) {
            $resultRedirect->setPath(
                '*/*/new',
                [
                    '_current' => true,
                ]
            );
        } else {
            $resultRedirect->setPath('*/*/');
        }
    }
}
