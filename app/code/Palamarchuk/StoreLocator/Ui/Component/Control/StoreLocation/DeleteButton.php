<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Ui\Component\Control\StoreLocation;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    public function getButtonData(): array
    {
        if ($this->getStoreLocationId()) {
            return [
                'id' => 'delete',
                'label' => __('Delete'),
                'on_click' => "deleteConfirm('" . __('Are you sure you want to delete this store location?') . "', '"
                    . $this->getUrl('*/*/delete', ['id' => $this->getStoreLocationId()]) . "', {data: {}})",
                'class' => 'delete',
                'sort_order' => 10
            ];
        }
        return [];
    }
}

