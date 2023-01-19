<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Model;
/**
 * Pay In Store payment method model
 */
class RedirectPaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'elogic_redirect_payment';
}
