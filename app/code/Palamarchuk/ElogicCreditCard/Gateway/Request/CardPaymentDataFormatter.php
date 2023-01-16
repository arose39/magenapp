<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Request;
/**
* To formats payment data according to CC api requirements
* Class PaymentDataFormatter
 */
class CardPaymentDataFormatter
{
    /**
     * @param string $value
     * @return string
     */
    public function getFormattedYear($value)
    {
        return substr($value, -2);
    }

    /**
     * @param string $value
     * @return string
     */
    public function getFormattedMonth($value)
    {
        return strlen($value) < 2 ? '0' . $value : $value;
    }
}
