<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Model;

use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\Api\Search\SearchResultFactory;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxFactory;
use Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax as LuxuryTaxResource;
use Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax\CollectionFactory;

class LuxuryTaxRepository
{
    public function __construct(
        private LuxuryTaxFactory      $luxuryTaxFactory,
        private CollectionFactory     $collectionFactory,
        private LuxuryTaxResource     $luxuryTaxResource,
        private SearchResultFactory   $searchResultFactory,
        private CollectionProcessorInterface     $collectionProcessor
    ) {
    }

    public function get(int $id): LuxuryTax
    {
        $object = $this->luxuryTaxFactory->create();
        $this->luxuryTaxResource->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Unable to find work schedule entity with ID "%1"', $id));
        }

        return $object;
    }

    public function getList(SearchCriteriaInterface $searchCriteria = null): SearchResultInterface
    {
        /** @var \Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax\Collection $collection */
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
     * @throws AlreadyExistsException
     */
    public function save(LuxuryTax $luxuryTax): LuxuryTax
    {
        $this->luxuryTaxResource->save($luxuryTax);

        return $luxuryTax;
    }


    /**
     * @throws StateException
     */
    public function delete(LuxuryTax $luxuryTax): bool
    {
        try {
            $this->luxuryTaxResource->delete($luxuryTax);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove entity #%1 ', $luxuryTax->getId()));
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
