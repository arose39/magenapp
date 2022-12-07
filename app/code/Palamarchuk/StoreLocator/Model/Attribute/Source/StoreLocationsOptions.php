<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class StoreLocationsOptions extends AbstractSource
{
    public function __construct(
        private StoreLocationRepository $storeLocationRepository,
        private SortOrderBuilder $sortOrderBuilder,
        private SearchCriteriaBuilder  $searchCriteriaBuilder
    ) {
    }

    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function getAllOptions()
    {
        $sortOrder = $this->sortOrderBuilder->setField('name')->setDirection('ASC')->create();
        $searchCriteria = $this->searchCriteriaBuilder->setSortOrders([$sortOrder])->create();
        $storeLocations = $this->storeLocationRepository->getList($searchCriteria)->getItems();
        $locations = [];
        $locations[] = ['value' => '', 'label' => 'store location not chosen'];
        foreach ($storeLocations as $location) {
            $locations[] = ['value' => $location->getId(), 'label' => $location->getName()];
        }

        return $locations;
    }
}
