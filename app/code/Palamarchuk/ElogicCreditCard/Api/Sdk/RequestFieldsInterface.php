<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Api\Sdk;

interface RequestFieldsInterface
{
    const VERSION = 'version';
    const PUBLIC_KEY = 'public_key';
    const ACTION = 'action';
    const AMOUNT = 'amount';
    const CARD = 'card';
    const CARD_CVV = 'card_cvv';
    const CARD_EXP_MONTH = 'card_exp_month';
    const CARD_EXP_YEAR = 'card_exp_year';
    const CURRENCY = 'currency';
    const DESCRIPTION = 'description';
    const PHONE = 'phone';
    const ORDER_ID = 'order_id';
}
