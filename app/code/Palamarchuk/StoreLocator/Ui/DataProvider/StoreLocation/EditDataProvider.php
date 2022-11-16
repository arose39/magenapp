<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Ui\DataProvider\StoreLocation;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;

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
    ) {
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

            if ($model->getStoreImg()) {
                $m[0]['name'] = $model->getStoreImg();
                $m[0]['url'] = $this->getMediaUrl().$model->getStoreImg();
                $this->loadedData[$model->getId()]['general']['store_img'] = $m;
            }
            if ($workSchedule = $model->getWorkSchedule()) {
                $m['monday_from'] = explode(' - ', $workSchedule->getMonday())[0];
                $m['monday_to'] = explode(' - ', $workSchedule->getMonday())[1];
                $m['tuesday_from'] = explode(' - ', $workSchedule->getTuesday())[0];
                $m['tuesday_to'] = explode(' - ', $workSchedule->getTuesday())[1];
                $m['wednesday_from'] = explode(' - ', $workSchedule->getWednesday())[0];
                $m['wednesday_to'] = explode(' - ', $workSchedule->getWednesday())[1];
                $m['thursday_from'] = explode(' - ', $workSchedule->getThursday())[0];
                $m['thursday_to'] = explode(' - ', $workSchedule->getThursday())[1];
                $m['friday_from'] = explode(' - ', $workSchedule->getFriday())[0];
                $m['friday_to'] = explode(' - ', $workSchedule->getFriday())[1];
                $m['saturday_from'] = explode(' - ', $workSchedule->getSaturday())[0];
                $m['saturday_to'] = explode(' - ', $workSchedule->getSaturday())[1];
                $m['sunday_from'] = explode(' - ', $workSchedule->getSunday())[0];
                $m['sunday_to'] = explode(' - ', $workSchedule->getSunday())[1];
                $this->loadedData[$model->getId()]['work_schedule'] = $m;
            }
        }

        return $this->loadedData;
    }

    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'store_locator/store_images/';
        return $mediaUrl;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }
}
