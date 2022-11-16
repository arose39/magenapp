<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Context;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\Collection as StoreLocationsCollection;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;

class StoresViewModel implements ArgumentInterface
{
    public function __construct(
        private CollectionFactory $collectionFactory,
        private Http              $request,
        private Context           $context
    ) {
    }

    /**
     * @return StoreLocationsCollection
     */
    public function getPaginatedStoreLocations()
    {
        $page = ($this->request->getParam('p')) ? $this->request->getParam('p') : 1;
        $limit = ($this->request->getParam('limit')) ? $this->request->getParam('limit') : 20;

        return $this->collectionFactory
            ->create()
            ->setOrder('name', 'ASC')
            ->setPageSize($limit)
            ->setCurPage($page);
    }

    /**
     * @return StoreLocationsCollection
     */
    public function getAllStoreLocations(): StoreLocationsCollection
    {
        return $this->collectionFactory
            ->create()
            ->setOrder('name', 'ASC');
    }

    /**
     * @return string
     */
    public function getPager(): string
    {
        return $this->context->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'store.locations.pager')
            ->setAvailableLimit([2 => 2, 10 => 10, 15 => 15, 20 => 20, 50 => 50])
            ->setShowPerPage(true)
            ->setCollection($this->getPaginatedStoreLocations())->toHtml();
    }
}
