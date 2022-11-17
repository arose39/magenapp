<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Model\ResourceModel\StoreLocation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Palamarchuk\LuxuryTax\Model\LuxuryTax;
use Palamarchuk\LuxuryTax\Model\ResourceModel\LuxuryTax as LuxuryTaxResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'luxury_tax_id';

    protected function _construct()
    {
        $this->_init(
            LuxuryTax::class,
            LuxuryTaxResource::class
        );
    }

}
