<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationSearchResult;

interface StoreLocationRepositoryInterface
{
    /**
     * @param int $id
     * @return StoreLocation
     */
    public function get(int $id): StoreLocation;

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return StoreLocationSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null): StoreLocationSearchResultInterface;

    /**
     * @param StoreLocation $storeLocation
     * @return StoreLocation
     */
    public function save(StoreLocation $storeLocation): StoreLocationInterface;

    /**
     * @return StoreLocation
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveFromApi(): StoreLocation;

    /**
     * @param int $id
     * @return StoreLocation
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateFromApi(int $id): StoreLocation;

    /**
     * @param StoreLocation $storeLocation
     * @return bool
     */
    public function delete(StoreLocation $storeLocation): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;
}
