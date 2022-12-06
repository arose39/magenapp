<?php

declare(strict_types=1);

namespace Palamarchuk\StoreLocator\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\ScopeInterface;
use Palamarchuk\StoreLocator\Model\StoreLocationFactory;
use Palamarchuk\StoreLocator\Model\StoreLocationRepository;


class CreateStoreLocation implements ResolverInterface
{
    public function __construct(
        private StoreLocationRepository $storeLocationRepository,
        private StoreLocationFactory $storeLocationFactory
    ) {
    }


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

//        if (isset($args['input']['date_of_birth'])) {
//            $args['input']['dob'] = $args['input']['date_of_birth'];
//        }
        $data = ['id' => $args['input']['id'], 'name' =>$args['input']['name']];

        return  ['storeLocation' => $data];
    }
}
