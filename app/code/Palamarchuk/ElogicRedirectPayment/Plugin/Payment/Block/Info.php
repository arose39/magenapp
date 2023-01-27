<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicRedirectPayment\Plugin\Payment\Block;

use Palamarchuk\ElogicRedirectPayment\Api\Data\PaymentMethodCodeInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info as BlockInfo;
use Palamarchuk\ElogicRedirectPayment\Api\Sdk\ResponseFieldsInterface;

/**
 * Class Info
 */
class Info
{
    /**
     * @var array
     */
    private $labels = [
        ResponseFieldsInterface::SENDER_CARD_MASK => 'Card Number',
        ResponseFieldsInterface::PAY_TYPE => 'Payment Type',
        ResponseFieldsInterface::SENDER_CARD_TYPE => 'Type',
        ResponseFieldsInterface::SENDER_CARD_BANK => 'Bank',
        ResponseFieldsInterface::ACQUIRER_ID => 'Acquirer ID',
        ResponseFieldsInterface::PAYMENT_ID => 'Payment ID',
        ResponseFieldsInterface::LIQPAY_ORDER_ID => 'LiqPay Order ID',
    ];

    /**
     * @var array
     */
    private $values = [];

    /**
     * @param BlockInfo $subject
     * @param $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetSpecificInformation(
        BlockInfo $subject,
                  $result
    )
    {
        if (PaymentMethodCodeInterface::CODE === $subject->getInfo()->getMethod()) {
            $payment = $subject->getInfo();
            $additionalData = $payment->getAdditionalInformation()['raw_details_info'];
            foreach ($this->labels as $key => $label) {
                if (array_key_exists($key, $additionalData)) {
                    $value = $additionalData[$key];
                    if (isset($this->values[$key][$value])) {
                        $value = $this->values[$key][$value];
                    }
                    $result[$label] = $value;
                }
            }
        }

        return $result;
    }
}
