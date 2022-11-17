<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Plugin;

use Geocoder\Exception\Exception;
use Geocoder\Location;
use Geocoder\Provider\GoogleMaps\GoogleMaps;
use Geocoder\Query\GeocodeQuery;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use Palamarchuk\StoreLocator\Model\ModuleConfig;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class Geocoder
{
    private Client $httpClient;
    private GoogleMaps $provider;
    private StatefulGeocoder $geocoder;
    private ModuleConfig $moduleConfig;

    public function __construct(
        Client       $httpClient,
        ModuleConfig $moduleConfig
    ) {
        $this->moduleConfig = $moduleConfig;
        $this->httpClient = $httpClient;
        $apiKey = $this->moduleConfig->getMapApi();
        $this->provider = new GoogleMaps(
            $this->httpClient,
            'en',
            $apiKey
        );
        $this->geocoder = new StatefulGeocoder($this->provider);
    }

    /**
     * @param StoreLocationRepository $storeLocationRepository
     * @param StoreLocation $storeLocation
     * @return void
     * @throws Exception
     */
    public function beforeSave(
        StoreLocationRepository $storeLocationRepository,
        StoreLocation $storeLocation
    ): void {
        if (!$storeLocation->getLatitude() || !$storeLocation->getLongitude()) {
            $fullAddress = $this->createFullAddress(
                $storeLocation->getAddress(),
                $storeLocation->getCity(),
                $storeLocation->getCountry(),
                $storeLocation->getZip()
            );
            if ($this->moduleConfig->checkAddressIsRealEnabled()) {
                $this->checkAddressIsExists($fullAddress);
            }

            $coordinates = $this->getCoordinates($fullAddress);
            $storeLocation->setLatitude($coordinates['latitude']);
            $storeLocation->setLongitude($coordinates['longitude']);
        }
    }

    /**
     * @param string $address
     * @param string $city
     * @param string $country
     * @param string $zip
     * @return string
     */
    private function createFullAddress(
        string $address,
        string $city,
        string $country,
        string $zip
    ): string {
        return $address . ", " .
            $city . ", " .
            $country . ", " .
            $zip;
    }

    /**
     * @param string $fullAddress
     * @return array
     * @throws Exception
     */
    private function getCoordinates(string $fullAddress): array
    {
        $result = $this->getLocationResult($fullAddress);
        $coordinates['latitude'] = $result->getCoordinates()->getLatitude();
        $coordinates['longitude'] = $result->getCoordinates()->getLongitude();

        return $coordinates;
    }

    /**
     * @param string $fullAddress
     * @return bool
     * @throws Exception
     */
    private function checkAddressIsExists(string $fullAddress): bool
    {
        $zipFromFullAddress = $this->getZipFromFullAddress($fullAddress);
        $result = $this->getLocationResult($fullAddress);
        $errors = [];
        if (!$result->getCountry()->getCode()) {
            $errors[] = 'There is not such country.';
        }
        if (!$this->checkZipPatternIsCorrect($result->getPostalCode(), $zipFromFullAddress)) {
            $errors[] = "Zip code pattern is incorrect. For this address zip code pattern should be like " . $result->getPostalCode();
        }
        if (!$result->getLocality()) {
            $errors[] = 'There is not city with such name.';
        }
        if (!$result->getStreetName()) {
            $errors[] = 'There is not street with such name.';
        }
        if (!$result->getStreetNumber()) {
            $errors[] = 'There is not house with such number.';
        }
        $errorsMessage = implode(PHP_EOL, $errors);
        if (!($errorsMessage === "")) {
            throw new \InvalidArgumentException($errorsMessage);
        }

        return true;
    }

    /**
     * @param string $fullAddress
     * @return string
     */
    private function getZipFromFullAddress(string $fullAddress): string
    {
        $pieces = explode(' ', $fullAddress);
        $lastWord = array_pop($pieces);

        return $lastWord;
    }

    /**
     * @param $correctZip
     * @param $testedZip
     * @return bool
     */
    private function checkZipPatternIsCorrect($correctZip, $testedZip): bool
    {
        if (strlen($correctZip) != strlen($testedZip)) {
            return false;
        }
        $zipLength = strlen($correctZip);
        $correctZipArray = str_split($correctZip);
        $testedZipArray = str_split($testedZip);
        for ($i = 0; $i < $zipLength; $i++) {
            if (is_int($correctZipArray[$i]) != is_int($testedZipArray[$i])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $fullAddress
     * @return Location
     * @throws Exception
     */
    private function getLocationResult(string $fullAddress): Location
    {
        return $this->geocoder->geocodeQuery(GeocodeQuery::create($fullAddress))->get(0);
    }
}
