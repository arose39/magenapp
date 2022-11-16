<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model;

use Magento\Framework\Api\SearchResults;
use Palamarchuk\StoreLocator\Api\StoreLocationSearchResultInterface;

class StoreLocationSearchResult extends SearchResults implements StoreLocationSearchResultInterface
{
}
