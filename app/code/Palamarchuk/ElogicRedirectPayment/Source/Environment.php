<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Palamarchuk\ElogicRedirectPayment\Api\Data\EnvironmentStatusInterface;

/**
 * Class Environment
 */
class Environment implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => EnvironmentStatusInterface::PRODUCTION, 'label' => __('Production')],
            ['value' => EnvironmentStatusInterface::SANDBOX, 'label' => __('Sandbox')],
        ];
    }
}
