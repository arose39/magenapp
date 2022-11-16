<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class SingleStoreViewModel implements ArgumentInterface
{
    public function __construct(
        private StoreLocationRepository $storeLocationRepository,
        private Http $request
    ) {
    }

    /**
     * @return \Palamarchuk\StoreLocator\Model\StoreLocation
     * @throws NoSuchEntityException
     */
    public function getSingleStoreLocation(): StoreLocation
    {
        $id = $this->request->get('id');

        return $this->storeLocationRepository->getByStoreUrlKey($id);
    }
}
