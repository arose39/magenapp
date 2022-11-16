<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class InstallDummiesStoreLocations implements DataPatchInterface
{
    public function __construct(
        private ModuleDataSetupInterface $moduleDataSetup,
    ) {
    }

    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        for ($i = 0; $i < 20; $i++) {
            $this->moduleDataSetup->getConnection()->insert(
                $this->moduleDataSetup->getTable('store_locations_entity'),
                [
                    'name' => 'store ' . "$i",
                    'description' => 'description ' . "$i",
                    'address' => 'address ' . "$i",
                    'city' => 'city ' . "$i",
                    'country' => 'US',
                    'state' => 1 + $i,
                    'zip' => 77777 + $i,
                    'phone' => 'phone number' . "$i",
                    'latitude' => 48.904383 + $i / 1000,
                    'longitude' => 24.768027 + $i / 1000,
                ]
            );
        }
        $this->moduleDataSetup->endSetup();
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}
