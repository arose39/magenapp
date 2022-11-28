<?php

namespace Palamarchuk\LuxuryTax\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class OrdersColourLuxuryTax extends Column
{

    public function __construct(
        ContextInterface   $context,
        UiComponentFactory $uiComponentFactory,
        array              $components = [],
        array              $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {
                $cellColour = $this->getLuxuryTaxCellColour($item['luxury_tax_amount']);
                $item['luxury_tax_amount'] = "<div style='background-color:$cellColour'>" .
                    $item['luxury_tax_amount'] .
                    '</div>';

            }
        }

        return $dataSource;
    }

    private function getLuxuryTaxCellColour(?float $luxuryTaxAmount): string
    {
        if ($luxuryTaxAmount === null){
            return "#fff0b7";
        }

        if ($luxuryTaxAmount < 100) {
            $cellColour = "#fff0b7";
        } elseif ($luxuryTaxAmount > 1000) {
            $cellColour = "green";
        } else {
            $cellColour = "yellow";
        }

        return $cellColour;
    }
}
