<?php

namespace Palamarchuk\StoreLocator\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Palamarchuk\StoreLocator\Model\ResourceModel\StoreLocation\CollectionFactory;
use Palamarchuk\StoreLocator\Model\StoreLocation as StoreLocationModel;

class StoreLocation implements ResolverInterface
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
            return ['items' => null];
        }

        $items = [];

        /** @var StoreLocationModel $storeLocation */
        foreach ($collection->getItems() as $storeLocation) {
            $items[] = [
                'id' => $storeLocation->getId(),
                'name' => $storeLocation->getName(),
            ];
        }

        return ['items' => $items];
    }
}
