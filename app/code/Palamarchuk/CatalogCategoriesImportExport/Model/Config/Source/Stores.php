<?php

declare(strict_types=1);

namespace Palamarchuk\CatalogCategoriesImportExport\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Store\Model\StoreManagerInterface;

class Stores implements OptionSourceInterface
{
    public function __construct(
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Get available options
     *
     * @codeCoverageIgnore
     * @return array
     */
    public function toOptionArray(): array
    {
        $allStores = $this->storeManager->getStores();
        $stores =[];
        $stores[] = ['value' => 0, 'label' => 'All store views'];
        foreach ($allStores as $store) {
            $stores[] = ['value' => $store->getId(), 'label' => $store->getName()];
        }

        return $stores;
    }
}
