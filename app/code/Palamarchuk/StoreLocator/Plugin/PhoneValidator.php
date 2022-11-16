<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Plugin;

use Palamarchuk\StoreLocator\Model\PhoneValidator as InternationalPhoneValidator;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class PhoneValidator
{
    public function __construct(
        private InternationalPhoneValidator $phoneValidator
    ) {
    }

    public function beforeSave(
        StoreLocationRepository $storeLocationRepository,
        StoreLocation           $storeLocation
    ): void {
        if (!$this->phoneValidator->validate($storeLocation->getPhone())) {
            $errorsMessage = 'Phone number must be in international format (e.g. +380633123456)';
            throw new \InvalidArgumentException($errorsMessage);
        }
    }
}
