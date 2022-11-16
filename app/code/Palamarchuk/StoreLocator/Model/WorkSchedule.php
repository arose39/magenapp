<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Store\Model\StoreManagerInterface;
use Palamarchuk\StoreLocator\Model\ResourceModel\WorkSchedule as WorkScheduleResource;

class WorkSchedule extends AbstractExtensibleModel
{
    const ID = 'work_schedule_id';
    protected StoreManagerInterface $storeManager;

    protected function _construct()
    {
        $this->_init(WorkScheduleResource::class);
    }
}
