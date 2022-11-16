<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\ResourceModel\WorkSchedule;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Palamarchuk\StoreLocator\Model\ResourceModel\WorkSchedule as WorkScheduleResource;
use Palamarchuk\StoreLocator\Model\WorkSchedule;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'store_location_id';

    protected function _construct()
    {
        $this->_init(
            WorkSchedule::class,
            WorkScheduleResource::class
        );
    }

}
