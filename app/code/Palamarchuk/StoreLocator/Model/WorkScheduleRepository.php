<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Palamarchuk\StoreLocator\Model\ResourceModel\WorkSchedule as WorkScheduleResource;
use Palamarchuk\StoreLocator\Model\WorkScheduleFactory;
use Palamarchuk\StoreLocator\Model\ResourceModel\WorkSchedule\CollectionFactory;

class WorkScheduleRepository
{
    public function __construct(
        private WorkScheduleFactory $workScheduleFactory,
        private CollectionFactory   $collectionFactory,
        private WorkScheduleResource $workScheduleResource,
        private SearchCriteriaBuilder            $searchCriteriaBuilder,
        private SearchResultFactory $searchResultFactory
    ) {
    }

    public function get(int $id): WorkSchedule
    {
        $object = $this->workScheduleFactory->create();
        $this->workScheduleResource->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Unable to find work schedule entity with ID "%1"', $id));
        }

        return $object;
    }

    /**
     * @param int $id
     * @return WorkSchedule
     * @throws NoSuchEntityException
     */
    public function getByStoreLocationId(int $id): WorkSchedule
    {
        $object = $this->workScheduleFactory->create();
        $this->workScheduleResource->load($object, $id, 'store_location_id');
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Unable to find work schedule entity with storeLocation ID "%1"', $id));
        }

        return $object;
    }

    public function getList(SearchCriteriaInterface $searchCriteria = null): SearchResultInterface
    {
        $collection = $this->collectionFactory->create();
        $searchCriteria = $this->searchCriteriaBuilder->create();

        if (null === $searchCriteria) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
        } else {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                    $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                }
            }
        }
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setSearchCriteria($searchCriteria);

        return $searchResult;
    }

    public function save(WorkSchedule $workSchedule): WorkSchedule
    {
        $this->workScheduleResource->save($workSchedule);

        return $workSchedule;
    }

    /**
     * @throws StateException
     */
    public function delete(WorkSchedule $workSchedule): bool
    {
        try {
            $this->workScheduleResource->delete($workSchedule);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove entity #%1 ', $workSchedule->getId()));
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
