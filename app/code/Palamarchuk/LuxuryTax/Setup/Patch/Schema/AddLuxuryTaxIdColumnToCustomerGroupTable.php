<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Setup\Patch\Schema;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class AddLuxuryTaxIdColumnToCustomerGroupTable implements SchemaPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $this->moduleDataSetup->getConnection()->addColumn(
            $this->moduleDataSetup->getTable('customer_group'),
            'luxury_tax_id',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'after' => 'tax_class_id',
                'comment'  => 'luxury tax id',
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
