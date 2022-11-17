<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LuxuryTax extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('luxury_tax_entity', 'luxury_tax_id');
    }
}
