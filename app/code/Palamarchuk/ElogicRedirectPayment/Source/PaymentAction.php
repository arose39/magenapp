<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PaymentAction
 */
class PaymentAction implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'authorize', 'label' => __('Authorize Only')],
            ['value' => 'authorize_capture', 'label' => __('Authorize and Capture')],
        ];
    }
}
