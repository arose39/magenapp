<?php

declare(strict_types=1);

namespace Palamarchuk\ElogicCreditCard\Gateway\Converter;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Palamarchuk\ElogicCreditCard\Gateway\Request\Encoder;
use Palamarchuk\ElogicCreditCard\Gateway\Request\SignatureFactory;

/**
 * Class ArrayToEncoded
 */
class ArrayToEncoded implements ConverterInterface
{
    /**
     * @var Encoder
     */
    private $encoder;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var SignatureFactory
     */
    private $signatureFactory;

    /**
     * ArrayToEncoded constructor.
     * @param Encoder $encoder
     * @param Json $serializer
     * @param SignatureFactory $signatureFactory
     */
    public function __construct(
        Encoder $encoder,
        Json $serializer,
        SignatureFactory $signatureFactory
    ) {
        $this->encoder = $encoder;
        $this->serializer = $serializer;
        $this->signatureFactory = $signatureFactory;
    }

    /**
     * @param array $response
     * @return array|string
     */
    public function convert($response)
    {
        $data = $response['data'];

        $encryptedData = $this->encoder->encode($this->serializer->serialize($data));

        return http_build_query([
            'data' => $encryptedData,
            'signature' => $this->signatureFactory->create($encryptedData),
        ]);
    }
}
