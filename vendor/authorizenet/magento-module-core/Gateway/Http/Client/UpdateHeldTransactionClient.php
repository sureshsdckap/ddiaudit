<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

class UpdateHeldTransactionClient extends AbstractClient
{
    
    /**
     * Operate in order using data which came from authorize.net.
     *
     * @param $request
     * @return \net\authorize\api\contract\v1\ANetApiResponseType
     * @throws \Exception
     */
    public function process($request)
    {
        return $this
            ->prepareAnetRequest($request, \AuthorizeNet\Core\Service\AnetRequestProxy::TYPE_UPDATE_HELD_TRANSACTION)
            ->executeWithApiResponse($this->getEndpointUrl());
    }
}
