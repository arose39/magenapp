<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Converter;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;

/**
 * Class ArrayToJson
 */
class ArrayToJson implements ConverterInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ArrayToJson constructor.
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param array $response
     * @return array|bool|string
     */
    public function convert($response)
    {
        return $this->serializer->serialize($response);
    }
}
