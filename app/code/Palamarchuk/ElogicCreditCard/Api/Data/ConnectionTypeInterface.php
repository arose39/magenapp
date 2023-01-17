<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Api\Data;

interface ConnectionTypeInterface
{
    const BUILT_IN_FORM = 'built_in_form';
    const WIDGET = 'widget';
    const REDIRECT = 'redirect';
}
