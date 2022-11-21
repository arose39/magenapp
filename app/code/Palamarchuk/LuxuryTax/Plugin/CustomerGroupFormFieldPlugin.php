<?php

namespace Palamarchuk\LuxuryTax\Plugin;

use Palamarchuk\LuxuryTax\Model\Config\Source\LuxuryTaxes;

class CustomerGroupFormFieldPlugin
{
    public function __construct(
        private LuxuryTaxes $luxuryTaxes
    )
    {
    }

    public function afterSetForm(
        \Magento\Customer\Block\Adminhtml\Group\Edit\Form $form)
    {
        $form = $form->getForm();
        $fs = $form->getElement('base_fieldset');

        $fs->addField(
            'luxury_tax_id',
            'select',
            [
                'name' => 'luxury_tax',
                'label' => __('Luxury tax'),
                'title' => __('Luxury tax'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->luxuryTaxes->toOptionArray(),
            ]
        );

        $form->addValues(
            [
                'luxury_tax_id' => 7,

            ]
        );

        return $form;
    }
}
