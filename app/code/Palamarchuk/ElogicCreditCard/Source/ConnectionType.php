<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Palamarchuk\ElogicCreditCard\Api\Data\ConnectionTypeInterface;

/**
 * Class ConnectionType
 * @api
 */
class ConnectionType implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => ConnectionTypeInterface::BUILT_IN_FORM, 'label' => __('Built-in form')],
        ];
    }
}
