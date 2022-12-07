<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Palamarchuk\StoreLocator\Api\StoreLocationRepositoryInterface;
use Palamarchuk\StoreLocator\Api\StoreLocationSearchResultInterface;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation as StoreLocationResource;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;

class StoreLocationRepository implements StoreLocationRepositoryInterface
{
    public function __construct(
        private StoreLocationFactory             $storeLocationFactory,
        private CollectionFactory                $collectionFactory,
        private StoreLocationResource            $storeLocationResource,
        private StoreLocationSearchResultFactory $searchResultFactory,
        private SearchCriteriaBuilder            $searchCriteriaBuilder,
        private RequestInterface                 $request,
        private ImageUploader                    $imageUploader,
        private CollectionProcessorInterface     $collectionProcessor
    )
    {
    }

    /**
     * @throws NoSuchEntityException
     */
    public function get(int $id): StoreLocation
    {
        $object = $this->storeLocationFactory->create();
        $this->storeLocationResource->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Unable to find entity with ID "%1"', $id));
        }

        return $object;
    }

    // If LuxuryTax don`t has store_url_key, search by ID.
    public function getByStoreUrlKey(string|int $storeUrlKey): StoreLocation
    {
        $object = $this->storeLocationFactory->create();
        $this->storeLocationResource->load($object, $storeUrlKey, 'store_url_key');
        if (!$object->getId()) {
            $this->storeLocationResource->load($object, $storeUrlKey);
        }
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Unable to find entity with store_url_key or with ID "%1"', $storeUrlKey));
        }

        return $object;
    }

    public function getList(?SearchCriteriaInterface $searchCriteria = null): StoreLocationSearchResultInterface
    {
        /** @var \Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\Collection $collection */
        $collection = $this->collectionFactory->create();
        if ($searchCriteria) {
            $this->collectionProcessor->process($searchCriteria, $collection);
        }
        $collection->load();
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        if ($searchCriteria) {
            $searchResult->setSearchCriteria($searchCriteria);
        }

        return $searchResult;
    }

    /**
     * @return StoreLocation
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveFromApi(): StoreLocation
    {
        $requestData = $this->request->getPostValue();
        $storeLocation = $this->storeLocationFactory->create();

        $storeLocation->setName($requestData['name']);
        $storeLocation->setStoreUrlKey($requestData['store_url_key']);
        $storeLocation->setDescription($requestData['description']);
        $storeLocation->setAddress($requestData['address']);
        $storeLocation->setCity($requestData['city']);
        $storeLocation->setCountry($requestData['country']);
        $storeLocation->setState((int)$requestData['state']);
        $storeLocation->setZip($requestData['zip']);
        $storeLocation->setPhone($requestData['phone']);
        $storeLocation->setLatitude((float)$requestData['latitude']);
        $storeLocation->setLongitude((float)$requestData['longitude']);

        if (isset($_FILES['store_img'])) {
            $result = $this->imageUploader->saveImage('store_img');
            $imageName = $result['name'];
            $storeLocation->setStoreImg($imageName);
        }
        $this->save($storeLocation);

        return $storeLocation;
    }

    /**
     * @param int $id
     * @return StoreLocation
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateFromApi(int $id): StoreLocation
    {
        $requestData = $this->request->getPostValue();
        $storeLocation = $this->get($id);
        $storeLocation->setName($requestData['name']);
        $storeLocation->setStoreUrlKey($requestData['store_url_key']);
        $storeLocation->setDescription($requestData['description']);
        $storeLocation->setAddress($requestData['address']);
        $storeLocation->setCity($requestData['city']);
        $storeLocation->setCountry($requestData['country']);
        $storeLocation->setState((int)$requestData['state']);
        $storeLocation->setZip($requestData['zip']);
        $storeLocation->setPhone($requestData['phone']);
        $storeLocation->setLatitude((float)$requestData['latitude']);
        $storeLocation->setLongitude((float)$requestData['longitude']);

        if (isset($_FILES['store_img'])) {
            $result = $this->imageUploader->saveImage('store_img');
            $imageName = $result['name'];
            if ($storeLocation->getStoreImg()) {
                $this->imageUploader->deleteImage($storeLocation->getStoreImg());
            }
            $storeLocation->setStoreImg($imageName);
        }
        $this->save($storeLocation);

        return $storeLocation;
    }

    public function save(StoreLocation $storeLocation): StoreLocation
    {
        $this->storeLocationResource->save($storeLocation);

        return $storeLocation;
    }

    /**
     * @throws StateException
     */
    public function delete(StoreLocation $storeLocation): bool
    {
        try {
            if ($storeLocation->getStoreImg()) {
                $this->imageUploader->deleteImage($storeLocation->getStoreImg());
            }
            $this->storeLocationResource->delete($storeLocation);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove entity #%1 or entity image', $storeLocation->getId()));
        }

        return true;
    }

    /**
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById(int $id): bool
    {
        return $this->delete($this->get($id));
    }
}
