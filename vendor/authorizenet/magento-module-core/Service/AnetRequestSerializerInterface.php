<?php

namespace AuthorizeNet\Core\Service;

use net\authorize\api\contract\v1\ANetApiRequestType;
use net\authorize\api\contract\v1\ANetApiResponseType;

interface AnetRequestSerializerInterface
{
    /**
     * @param AnetApiRequestType $request
     * @return string
     * @throws \Exception
     */
    public function serialize($request);

    /**
     * @param string $response
     * @param string $type
     * @return AnetApiResponseType
     */
    public function deserialize($response, $type);

    /**
     * @param mixed $object
     * @return array
     */
    public function toArray($object);
}
