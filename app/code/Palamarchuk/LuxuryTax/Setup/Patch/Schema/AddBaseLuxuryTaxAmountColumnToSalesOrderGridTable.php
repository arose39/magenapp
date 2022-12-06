<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddBaseLuxuryTaxAmountColumnToSalesOrderGridTable implements SchemaPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('sales_order_grid'),
            'base_luxury_tax_amount',
            [
                'type' => Table::TYPE_DECIMAL,
                'length' => '12,4',
                'nullable' => true,
                'after' => 'base_grand_total',
                'comment'  => 'base luxury tax amount',
                'grid' => true
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
