<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Api\Data;

interface StoreLocationInterface
{

    /**
     * @return mixed
     */
    public function getId(): mixed;

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId(mixed $value): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self;

    /**
     * @return string|int
     */
    public function getStoreUrlKey(): string|int;

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreUrlKey(string $value): StoreLocationInterface;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string $value
     * @return $this
     */
    public function setDescription(string $value): self;

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string;

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreImg(string $value): self;

    /**
     * @return string
     */
    public function getAddress(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setAddress(string $value): self;

    /**
     * @return string
     */
    public function getCity(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setCity(string $value): self;

    /**
     * @return string
     */
    public function getCountry(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setCountry(string $value): self;

    /**
     * @return int|null
     */
    public function getState(): ?int;

    /**
     * @param int $value
     * @return $this
     */
    public function setState(int $value): self;

    /**
     * @return string
     */
    public function getZip(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setZip(string $value): self;

    /**
     * @return string|null
     */
    public function getPhone(): ?string;

    /**
     * @param $value
     * @return $this
     */
    public function setPhone($value): self;

    /**
     * @return float
     */
    public function getLatitude(): float;

    /**
     * @param float $value
     * @return $this
     */
    public function setLatitude(float $value): self;

    /**
     * @return float
     */
    public function getLongitude(): float;

    /**
     * @param float $value
     * @return $this
     */
    public function setLongitude(float $value): self;

    /**
     * @return string
     */
    public function getStoreImageUrl(): string;

    /**
     * @return \Palamarchuk\StoreLocator\Model\WorkSchedule|null
     */
    public function getWorkSchedule(): ?WorkSchedule;
}
