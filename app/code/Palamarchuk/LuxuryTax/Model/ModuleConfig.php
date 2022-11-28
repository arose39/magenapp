<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ModuleConfig
{
    const CONFIG_MODULE_PATH = 'luxury_tax';
    const CELLS_COLOUR_IN_ORDER_GRID_SETTINGS = 'cells_colour_in_order_grid_settings';

    public function __construct(
        private ScopeConfigInterface $scopeConfig
    ) {
    }

    public function getLess100Colour(): string
    {
        return $this->getCellsColourInOrderGridSettings('less_100');
    }

    public function getBigger100Less1000Colour(): string
    {
        return $this->getCellsColourInOrderGridSettings('bigger_100_less_1000');
    }

    public function getBigger1000Colour(): string
    {
        return $this->getCellsColourInOrderGridSettings('bigger_1000');
    }


    private function getCellsColourInOrderGridSettings(string $code): string
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfig(self::CELLS_COLOUR_IN_ORDER_GRID_SETTINGS . $code);
    }

    private function getConfig(string $config_path): string|int
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_MODULE_PATH . '/' . $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }
}
