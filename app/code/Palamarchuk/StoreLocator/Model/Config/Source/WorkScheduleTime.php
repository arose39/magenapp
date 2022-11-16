<?php

namespace Palamarchuk\StoreLocator\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class WorkScheduleTime implements OptionSourceInterface
{
    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'Not working day', 'label' => 'Not working day'],
            ['value' => '7.00', 'label' => '7.00'],
            ['value' => '7.30', 'label' => '7.30'],
            ['value' => '8.00', 'label' => '8.00'],
            ['value' => '8.30', 'label' => '8.30'],
            ['value' => '9.00', 'label' => '9.00'],
            ['value' => '9.30', 'label' => '9.30'],
            ['value' => '10.00', 'label' => '10.00'],
            ['value' => '10.30', 'label' => '10.30'],
            ['value' => '15.00', 'label' => '15.00'],
            ['value' => '15.30', 'label' => '15.30'],
            ['value' => '16.00', 'label' => '16.00'],
            ['value' => '16.30', 'label' => '16.30'],
            ['value' => '17.00', 'label' => '17.00'],
            ['value' => '17.30', 'label' => '17.30'],
            ['value' => '18.00', 'label' => '18.00'],
            ['value' => '18.30', 'label' => '18.30'],
            ['value' => '19.00', 'label' => '19.00'],
            ['value' => '19.30', 'label' => '19.30'],
            ['value' => '20.00', 'label' => '20.00'],
            ['value' => '20.30', 'label' => '20.30'],
            ['value' => '21.00', 'label' => '21.00'],
        ];
    }
}
