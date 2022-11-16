<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class StoreLocation extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('store_locations_entity', 'store_location_id');
    }
}
