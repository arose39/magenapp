<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Palamarchuk\LuxuryTax\Model\Config\Source\LuxuryTaxes;

class CustomerGroupFormFieldPlugin
{
    public function __construct(
        private LuxuryTaxes              $luxuryTaxes,
        private GroupRepositoryInterface $groupRepository
    )
    {
    }

    public function afterSetForm(
        \Magento\Customer\Block\Adminhtml\Group\Edit\Form $form): \Magento\Framework\Data\Form
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
        if (!$form->getElement('id')) {
            $luxuryTaxIdValue = '';
        } else {
            // Get $customerGroupId from id form field value
            $customerGroupId = $form->getElement('id')->getEscapedValue();
            $customerGroup = $this->groupRepository->getById($customerGroupId);
            $ea = $customerGroup->getExtensionAttributes();
            $ea->getLuxuryTaxId();
            $luxuryTaxIdValue = $ea->getLuxuryTaxId() ?? '';
        }
        $form->addValues(
            [
                'luxury_tax_id' => $luxuryTaxIdValue,
            ]
        );

        return $form;
    }
}
