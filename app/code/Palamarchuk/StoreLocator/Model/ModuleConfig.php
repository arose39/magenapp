<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ModuleConfig
{
    const CONFIG_MODULE_PATH = 'store_locator';
    const MAP_ENABLED = 'map_enabled';
    const MODULE_ENABLED = 'module_enabled';
    const CHECK_ADDRESS_IS_REAL_ENABLED = 'check_addres_is_real_enabled';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getMapApi(): string
    {
        return $this->getMapConfig('api_key');
    }

    public function getMapLatitude(): string
    {
        return $this->getMapConfig('map_default_latitude');
    }

    public function getMapLongitude(): string
    {
        return $this->getMapConfig('map_default_longitude');
    }

    public function getMapZoomDefault(): int
    {
        return ((int)$this->getMapConfig('zoom_default')) ?: 3;
    }

    public function mapIsEnabled(): bool
    {
        return (bool)$this->getConfig('general/' . self::MAP_ENABLED);
    }

    public function moduleIsEnabled(): bool
    {
        return (bool)$this->getConfig('general/' . self::MODULE_ENABLED);
    }

    public function checkAddressIsRealEnabled(): bool
    {
        return (bool)$this->getConfig('general/' . self::CHECK_ADDRESS_IS_REAL_ENABLED);
    }

    private function getMapConfig(string $code): string
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfig('map_setting' . $code);
    }

    private function getConfig(string $config_path): string|int
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_MODULE_PATH . '/' . $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
