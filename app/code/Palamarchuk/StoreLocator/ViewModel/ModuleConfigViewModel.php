<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Palamarchuk\StoreLocator\Model\ModuleConfig;

class ModuleConfigViewModel implements ArgumentInterface
{
    public function __construct(
        private ModuleConfig $moduleConfig
    ) {
    }

    public function getMapApi(): string
    {
        return $this->moduleConfig->getMapApi();
    }

    public function getMapLatitude(): string
    {
        return $this->moduleConfig->getMapLatitude();
    }

    public function getMapLongitude(): string
    {
        return $this->moduleConfig->getMapLongitude();
    }

    public function getMapZoomDefault(): int
    {
        return $this->moduleConfig->getMapZoomDefault();
    }

    public function mapIsEnabled(): bool
    {
        return $this->moduleConfig->mapIsEnabled();
    }

    public function moduleIsEnabled(): bool
    {
        return $this->moduleConfig->moduleIsEnabled();
    }
}
