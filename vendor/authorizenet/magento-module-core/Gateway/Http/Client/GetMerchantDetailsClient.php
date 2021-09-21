<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

use AuthorizeNet\Core\Service\AnetRequestProxy;

use net\authorize\api\contract\v1 as AnetAPI;

class GetMerchantDetailsClient extends AbstractClient
{

    /**
     * Process to generate the merchant details
     *
     * @param $request
     * @return AnetAPI\AnetApiResponseType
     * @throws \Exception
     */
    public function process($request)
    {
        return $this->prepareAnetRequest($request, AnetRequestProxy::TYPE_GET_MERCHANT_DETAILS)
            ->executeWithApiResponse($this->getEndpointUrl());
    }
}
