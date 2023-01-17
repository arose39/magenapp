<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Api\Data;

interface PaymentActionInterface
{
    const AUTHORIZE = 'authorize';
    const AUTHORIZE_CAPTURE = 'authorize_capture';
    const REFUND = 'refund';
}
