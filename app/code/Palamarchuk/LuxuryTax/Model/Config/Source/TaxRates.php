<?php

namespace Palamarchuk\LuxuryTax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\Calculation\Rate;

class TaxRates implements OptionSourceInterface
{
    public function __construct(
        private Rate $taxRatesModel
    )
    {
    }

    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        $allTaxRates = $this->taxRatesModel->getCollection()->getData();
        $taxes = [];
        foreach ($allTaxRates as $tax) {
            $taxes[] = ['value' => $tax["tax_calculation_rate_id"], 'label' => $tax["code"] . ' -- ' . $tax["rate"]];
        }

        return $taxes;

    }
}
