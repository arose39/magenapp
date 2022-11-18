<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Ui\DataProvider\LuxuryTax;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax\CollectionFactory;

class EditDataProvider extends AbstractDataProvider
{
    protected $loadedData = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->collection = $collectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    public function getDataSourceData(): array
    {
        return [];
    }

    public function getData()
    {
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()]['general'] = $model->getData();
        }

        return $this->loadedData;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
