<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use \Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation as StoreLocationResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'store_location_id';

    protected function _construct()
    {
        $this->_init(
            StoreLocation::class,
            StoreLocationResource::class
        );
    }

}
