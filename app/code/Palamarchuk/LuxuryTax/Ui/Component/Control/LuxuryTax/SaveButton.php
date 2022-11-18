<?php

declare(strict_types=1);

namespace Palamarchuk\LuxuryTax\Ui\Component\Control\LuxuryTax;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label' => __('Save luxury tax'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'luxury_tax_form.luxury_tax_form',
                                'actionName' => 'save',
                                'params' => [
                                    true
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 20,
            'class_name' => Container::SPLIT_BUTTON,
            'options' => $this->getOptions(),
        ];
    }

    protected function getOptions(): array
    {
        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'luxury_tax_form.luxury_tax_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'redirect_to_new' => '1'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & Continue Edit'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'luxury_tax_form.luxury_tax_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'back' => '1'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $options;
    }
}

