<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Plugin;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Block\Adminhtml\Group\Edit\Form;
use Palamarchuk\LuxuryTax\Model\Config\Source\Stores;

class CustomerGroupFormFieldPlugin
{
    public function __construct(
        private Stores                   $luxuryTaxes,
        private GroupRepositoryInterface $groupRepository
    )
    {
    }

    public function afterSetForm(Form $form): \Magento\Framework\Data\Form
    {
        $form = $form->getForm();
        $fs = $form->getElement('base_fieldset');

        $fs->addField(
            "luxury_tax_id",
            'select',
            [
                'name' => 'luxury_tax_id',
                'label' => __('Luxury tax'),
                'title' => __('Luxury tax'),
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
                'luxury_tax_id' => $luxuryTaxIdValue
            ]
        );

        return $form;
    }
}
