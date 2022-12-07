<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Palamarchuk\StoreLocator\Model\StoreLocation;
use Palamarchuk\StoreLocator\Model\StoreLocationFactory;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;

class UpdateStoreLocation implements ResolverInterface
{
    public function __construct(
        private StoreLocationRepository $storeLocationRepository,

    ) {
    }

    /**
     * @throws NoSuchEntityException
     * @throws GraphQlInputException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        /** @var StoreLocation $storeLocation */
        $storeLocation = $this->storeLocationRepository->get($args['input']['id']);
        $storeLocation->setData($args['input']);
        $result = $this->storeLocationRepository->save($storeLocation);
        // add alias 'id' to 'store_location_id'
        $result['id'] = $result->getId();

        return  ['storeLocation' => $result];
    }
}
