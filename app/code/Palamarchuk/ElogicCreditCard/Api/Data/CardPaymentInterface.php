<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Api\Data;

interface CardPaymentInterface
{
    const TYPE = 'cc_type';
    const EXPIRATION_YEAR = 'cc_exp_year';
    const EXPIRATION_MONTH = 'cc_exp_month';
    const NUMBER = 'cc_number';
    const CVV = 'cc_cid';
}
