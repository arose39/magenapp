<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Ui\Component\Control\LuxuryTax;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Palamarchuk\LuxuryTax\Ui\Component\Control\LuxuryTax\GenericButton;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData()
    {
        if ($this->getLuxuryTaxId()) {
            return [
                'id' => 'delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('" . __('Are you sure you want to delete this luxury tax?') . "', '"
                    . $this->getUrl('*/*/delete', ['id' => $this->getLuxuryTaxId()]) . "', {data: {}})",
                'class' => 'delete',
                'sort_order' => 10
            ];
        }
        return [];
    }
}

