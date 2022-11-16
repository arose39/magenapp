<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Ui\Component\Listing\Columns;

use Magento\Catalog\Helper\Image;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class StoreImage extends Column
{
    const ALT_FIELD = 'photo';
    const ENTITY_MEDIA_PATH = 'store_locator/store_images';
    const DEFAULT_STORE_IMAGE_NAME = 'default_store_image.jpeg';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Image $imageHelper
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = 'store_img';
            foreach ($dataSource['data']['items'] as &$item) {
                $url = '';
                if ($item[$fieldName] != '') {
                    $url = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ) . self::ENTITY_MEDIA_PATH . '/' . $item[$fieldName];
                } else {
                    $url = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ) . self::ENTITY_MEDIA_PATH . '/' . self::DEFAULT_STORE_IMAGE_NAME;
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_orig_src'] = $url;
            }
        }

        return $dataSource;
    }
}
