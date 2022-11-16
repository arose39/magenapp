<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Ui\DataProvider\StoreLocation;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;

class ListingDataProvider extends AbstractDataProvider
{
    private CollectionFactory $collectionFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData(): array
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        return $items;
    }
}
