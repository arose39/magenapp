<?php

namespace Palamarchuk\LuxuryTax\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Palamarchuk\LuxuryTax\Model\LuxuryTaxRepository;

class LuxuryTaxes implements OptionSourceInterface
{
    public function __construct(
        private LuxuryTaxRepository $luxuryTaxRepository
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
       $luxuryTaxes = $this->luxuryTaxRepository->getList()->getItems();
        $taxes =[];
        $taxes[] = ['value' => '', 'label' => '--- please select ---'];

        foreach ($luxuryTaxes as $tax) {
            $taxes[] = ['value' => $tax->getId(), 'label' => $tax->getName()];
        }

        return $taxes;

    }
}
