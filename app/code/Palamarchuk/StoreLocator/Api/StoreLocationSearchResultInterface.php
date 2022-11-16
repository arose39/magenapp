<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Api;

interface StoreLocationSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface[]
     */
    public function getItems();

    /**
     * @param \Palamarchuk\StoreLocator\Api\Data\StoreLocationInterface[] $items
     * @return void
     */
    public function setItems(array $items);

}
