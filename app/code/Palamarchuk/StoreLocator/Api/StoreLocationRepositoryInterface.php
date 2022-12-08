<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;
use Palamarchuk\StoreLocator\Model\StoreLocationSearchResult;

interface StoreLocationRepositoryInterface
{
    /**
     * @param int $id
     * @return \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface
     */
    public function get(int $id): \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface|null $searchCriteria
     * @return \Palamarchuk\StoreLocator\Api\StoreLocationSearchResultInterface
     */
    public function getList(?SearchCriteriaInterface $searchCriteria = null): StoreLocationSearchResultInterface;

    /**
     * @param \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface $storeLocation
     * @return \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface
     */
    public function save(\Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface $storeLocation): \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;

    /**
     * @return \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function saveFromApi(): \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;

    /**
     * @param int $id
     * @return \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function updateFromApi(int $id): \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;

    /**
     * @param \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface $storeLocation
     * @return bool
     */
    public function delete(\Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface $storeLocation): bool;

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById(int $id): bool;
}
