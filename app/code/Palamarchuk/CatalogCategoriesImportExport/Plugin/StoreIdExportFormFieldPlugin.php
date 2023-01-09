<?php

declare(strict_types=1);

namespace Palamarchuk\CatalogCategoriesImportExport\Plugin;

use Magento\ImportExport\Block\Adminhtml\Export\Edit\Form;
use Palamarchuk\CatalogCategoriesImportExport\Model\Config\Source\Stores;

class StoreIdExportFormFieldPlugin
{
    public function __construct(
        private Stores $stores
    )
    {
    }

    public function beforeSetForm(Form $subject, \Magento\Framework\Data\Form $form): array
    {
        $fs = $form->getElement('base_fieldset');

        $htmlIdPrefix = $subject->getHtmlIdPrefix();

        $fs->addField(
            "store_id",
            'select',
            [
                'name' => 'store_id',
                'label' => __('Store ID'),
                'title' => __('Store ID'),
                'values' => $this->stores->toOptionArray(),
            ]
        );

        $subject->setChild(
            'form_after',
            $subject->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap(
                "{$htmlIdPrefix}store_id",
                'store_id'
            )->addFieldMap(
                "{$htmlIdPrefix}entity",
                'entity'
            )->addFieldDependence(
                'store_id',
                'entity',
                'catalog_category'
            )
        );

        return [$form];
    }
}
