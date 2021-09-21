<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use net\authorize\api\contract\v1 as AnetAPI;

class GetMerchantDetailsRequestBuilder extends AbstractRequestBuilder
{

    /**
     * Build request to get Merchant details
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $loginId = $this->subjectReader->readLoginId($subject);
        $transKey = $this->subjectReader->readTransactionKey($subject);

        $anetRequest = new AnetAPI\GetMerchantDetailsRequest();

        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication
            ->setName($loginId)
            ->setTransactionKey($transKey);
        $anetRequest
            ->setMerchantAuthentication($merchantAuthentication);

        return ['request' => $anetRequest];
    }
}
