<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Ui\Component\Control\StoreLocation;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

abstract class GenericButton
{
    public function __construct(
        private UrlInterface            $urlBuilder,
        private RequestInterface        $request,
        private StoreLocationRepository $storeLocationRepository
    ) {
    }

    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getStoreLocationId()
    {
        $storeLocationId = (int)$this->request->getParam('id');
        if ($storeLocationId == null) {
            return 0;
        }
        $storeLocation = $this->storeLocationRepository->get($storeLocationId);

        return $storeLocation->getId() ?: null;
    }
}
