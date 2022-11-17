<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddLuxuryTaxAmountColumnToSalesOrderTable implements SchemaPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order'),
            'luxury_tax_amount',
            [
                'type' => Table::TYPE_FLOAT,
                'nullable' => true,
                'after' => 'tax_amount',
                'comment'  => 'luxury tax amount',
            ]
        );

        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
