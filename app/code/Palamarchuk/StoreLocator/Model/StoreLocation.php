<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Store\Model\StoreManagerInterface;
use Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation as StoreLocationResource;

class StoreLocation extends AbstractExtensibleModel implements StoreLocationInterface
{
    const ID = 'store_location_id';
    const ENTITY_MEDIA_PATH = 'store_locator/store_images';
    const DEFAULT_STORE_IMAGE_NAME = 'default_store_image.jpeg';
    protected StoreManagerInterface $storeManager;
    private WorkScheduleRepository $workScheduleRepository;
    private ImageUploader $imageUploader;

    public function __construct(
        Context                                       $context,
        \Magento\Framework\Registry                   $registry,
        ExtensionAttributesFactory                    $extensionFactory,
        AttributeValueFactory                         $customAttributeFactory,
        StoreLocationResource              $resource,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        WorkScheduleRepository                        $workScheduleRepository,
        ImageUploader $imageUploader,
        array                                         $data = [],
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);
        $this->workScheduleRepository = $workScheduleRepository;
        $this->imageUploader = $imageUploader;
    }

    /**
     * @return mixed|null
     */
    public function getId(): mixed
    {
        return $this->_getData(self::ID);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setId(mixed $value): self
    {
        $this->setData(self::ID, $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->_getData("name");
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setName(string $value): self
    {
        $this->setData("name", $value);

        return $this;
    }

    /**
     * @return string|int
     * If no store_url_key, return ID
     */
    public function getStoreUrlKey(): string|int
    {
        return ($this->_getData("store_url_key") ?? $this->_getData(self::ID));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreUrlKey(string $value): self
    {
        $this->setData("store_url_key", $value);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->_getData("description");
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setDescription(string $value): self
    {
        $this->setData("description", $value);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStoreImg(): ?string
    {
        return $this->_getData('store_img');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setStoreImg(string $value): self
    {
        $this->setData("store_img", $value);

        return $this;
    }

    /**
     * @return void
     */
    public function deleteStoreImgFile(): void
    {
        if ($this->getStoreImg()) {
            $this->imageUploader->deleteImage($this->getStoreImg());
        }
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->_getData('address');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAddress(string $value): self
    {
        $this->setData('address', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->_getData('city');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCity(string $value): self
    {
        $this->setData('city', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->_getData('country');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCountry(string $value): self
    {
        $this->setData('country', $value);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getState(): ?int
    {
        return (int)$this->_getData('state');
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setState(int $value): self
    {
        $this->setData('state', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->_getData('zip');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setZip(string $value): self
    {
        $this->setData('zip', $value);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->_getData('phone');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPhone($value): self
    {
        $this->setData('phone', $value);

        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return (float)$this->_getData('latitude');
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setLatitude(float $value): self
    {
        $this->setData('latitude', $value);

        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return (float)$this->_getData('longitude');
    }

    /**
     * @param float $value
     * @return $this
     */
    public function setLongitude(float $value): self
    {
        $this->setData('longitude', $value);

        return $this;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreImageUrl(): string
    {
        $url = '';
        $image = $this->getData('store_img');
        if ($image) {
            $url = $this->getStoreManager()->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . self::ENTITY_MEDIA_PATH . '/' . $image;
        } else {
            $url = $this->getStoreManager()->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . self::ENTITY_MEDIA_PATH . '/' . self::DEFAULT_STORE_IMAGE_NAME;
        }

        return $url;
    }

    /**
     * @return WorkSchedule|null
     */
    public function getWorkSchedule(): ?\Palamarchuk\StoreLocator\Model\WorkSchedule
    {
        try {
            $workSchedule = $this->workScheduleRepository->getByStoreLocationId((int)$this->getId());
        } catch (\Exception $exception) {
            return null;
        }

        return $workSchedule;
    }

    private function getStoreManager()
    {
        if (!isset($this->storeManager)) {
            $this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        }

        return $this->storeManager;
    }
}
