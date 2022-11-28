<?php

namespace Palamarchuk\LuxuryTax\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Palamarchuk\LuxuryTax\Model\ModuleConfig ;

class OrdersColourLuxuryTax extends Column
{
    private ModuleConfig $config;

    public function __construct(
        ModuleConfig $config,
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    )
    {
        $this->config = $config;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $cellColour = $this->getLuxuryTaxCellColour($item['base_luxury_tax_amount']);
                $item['base_luxury_tax_amount'] = "<div style='background-color:$cellColour'>" .
                    $item['base_luxury_tax_amount'] .
                    '</div>';

            }
        }

        return $dataSource;
    }

    private function getLuxuryTaxCellColour(?float $luxuryTaxAmount): string
    {
        if ($luxuryTaxAmount === null){
            return $this->config->getLess100Colour();
        }

        if ($luxuryTaxAmount < 100) {
            $cellColour = $this->config->getLess100Colour();
        } elseif ($luxuryTaxAmount > 1000) {
            $cellColour = $this->config->getBigger1000Colour();
        } else {
            $cellColour = $this->config->getBigger100Less1000Colour();
        }

        return $cellColour;
    }
}
