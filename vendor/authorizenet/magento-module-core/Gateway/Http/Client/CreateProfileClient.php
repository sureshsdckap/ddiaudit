<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

use AuthorizeNet\Core\Service\AnetRequestProxy;

use net\authorize\api\contract\v1 as AnetAPI;

class CreateProfileClient extends AbstractClient
{

    /**
     * Process for creating a profile
     *
     * @param $request
     * @return AnetAPI\AnetApiResponseType
     * @throws \Exception
     */
    public function process($request)
    {
        return $this
            ->prepareAnetRequest($request, AnetRequestProxy::TYPE_CREATE_CUSTOMER_PROFILE)
            ->executeWithApiResponse($this->getEndpointUrl());
    }
}
