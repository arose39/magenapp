<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Api\Sdk;

interface RequestFieldsInterface
{
    const VERSION = 'version';
    const PUBLIC_KEY = 'public_key';
    const ACTION = 'action';
    const AMOUNT = 'amount';
    const CARD = 'card';
    const CURRENCY = 'currency';
    const DESCRIPTION = 'description';
    const PHONE = 'phone';
    const ORDER_ID = 'order_id';
    const RESULT_URL = 'result_url';

    const SERVER_URL = 'server_url';
}
