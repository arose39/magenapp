<?php

namespace Palamarchuk\StoreLocator\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;
use Palamarchuk\StoreLocator\Model\StoreLocation as StoreLocationModel;

class StoreLocationsResolver implements ResolverInterface
{
    public function __construct(
        private CollectionFactory $collectionFactory
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|array[]
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    )
    {
        $collection = $this->collectionFactory->create();

        if (empty($collection)) {
            return ['storeLocations' => []];
        }

        $storeLocations = [];

        /** @var StoreLocationModel $storeLocation */
        foreach ($collection->getItems() as $storeLocation) {
            $storeLocations[] = [
                'id' => $storeLocation->getId(),
                'name' => $storeLocation->getName(),
                'store_url_key' => $storeLocation->getName(),
                'description' => $storeLocation->getStoreUrlKey(),
                'store_img' => $storeLocation->getStoreImageUrl(),
                'address' => $storeLocation->getAddress(),
                'city' => $storeLocation->getCity(),
                'country' => $storeLocation->getCountry(),
                'state' => $storeLocation->getState(),
                'zip' => $storeLocation->getZip(),
                'phone' => $storeLocation->getPhone(),
                'latitude' => $storeLocation->getLatitude(),
                'longitude' => $storeLocation->getLongitude(),
            ];
        }

        return ['storeLocations' => $storeLocations];
    }
}
