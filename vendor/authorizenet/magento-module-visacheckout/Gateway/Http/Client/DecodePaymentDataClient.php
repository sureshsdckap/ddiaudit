<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Gateway\Http\Client;

use AuthorizeNet\Core\Gateway\Http\Client\AbstractClient;
use AuthorizeNet\Core\Service\AnetRequestProxy;

use net\authorize\api\contract\v1 as AnetAPI;

class DecodePaymentDataClient extends AbstractClient
{
    /**
     * Prepare for Anet Request
     *
     * @param $request
     * @return AnetAPI\AnetApiResponseType
     * @throws \Exception
     */
    public function process($request)
    {
        return $this->prepareAnetRequest($request, AnetRequestProxy::TYPE_DECRYPT_PAYMENT_DATA)
            ->executeWithApiResponse($this->getEndpointUrl());
    }
}
