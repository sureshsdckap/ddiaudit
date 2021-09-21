<?php

namespace AuthorizeNet\Core\Service;

use net\authorize\util\Mapper;
use net\authorize\api\contract\v1\ANetApiRequestType;
use net\authorize\api\contract\v1\ANetApiResponseType;

class AnetRequestSerializerAdapter implements AnetRequestSerializerInterface
{
    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * SerializerAdapter constructor.
     */
    public function __construct()
    {
        $this->mapper = Mapper::Instance();
    }

    /**
     * @param ANetApiRequestType $request
     * @return string
     * @throws \Exception
     */
    public function serialize($request)
    {
        $requestTypeName = (new \ReflectionClass($request))->getName();
        $requestRoot = $this->mapper->getXmlName($requestTypeName);

        return json_encode([$requestRoot => $request]);
    }

    /**
     * @param string $response
     * @param string $type
     * @return ANetApiResponseType
     */
    public function deserialize($response, $type)
    {
        $decodedResponse = json_decode(substr($response,3), true);

        /** @var AnetApiResponseType $responseTypeInstance */
        $responseTypeInstance = (new $type());
        $responseTypeInstance->set($decodedResponse);

        return $responseTypeInstance;
    }

    /**
     * @param mixed $data
     * @return array
     * @throws \Exception
     */
    public function toArray($data)
    {
        return json_decode(json_encode($data), true);
    }
}
