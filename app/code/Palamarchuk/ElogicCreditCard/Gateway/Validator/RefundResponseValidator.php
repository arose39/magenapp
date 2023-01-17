<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Validator;
use Magento\Payment\Gateway\Validator\AbstractValidator;

/**
 * Class GeneralResponseValidator
 */
class RefundResponseValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        $response = $validationSubject['response'];

        $isValid = true;
        $errorMessages = [];

        foreach ($this->getResponseValidators() as $validator) {
            $validationResult = $validator($response);

            if (!$validationResult[0]) {
                $isValid = $validationResult[0];
                $this->addErrorMessages($errorMessages, $validationResult);
            }
        }

        return $this->createResult($isValid, $errorMessages);
    }

    /**
     * @param array $errorMessages
     * @param array $validationResult
     */
    private function addErrorMessages(array &$errorMessages, $validationResult)
    {
        $errorMessages = array_merge($errorMessages, $validationResult[1]);
    }

    /**
     * @return array
     */
    private function getResponseValidators()
    {
        return [
            function ($response) {
                return [
                    isset($response['action']),
                    [__('CCard Action is missing in the response')]
                ];
            },
            function ($response) {
                return [
                    isset($response['payment_id']),
                    [__('Ccard Payment Id is missing in the response')]
                ];
            },
            function ($response) {
                return [
                    isset($response['status']) && in_array($response['status'], ['success', 'reserved']),
                    [__('Ccard server returned an error in the response')]
                ];
            },
        ];
    }
}
