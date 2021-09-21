<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use net\authorize\api\contract\v1 as AnetAPI;

class GetTransactionDetailsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Build request to get Transaction details
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $anetRequest = new AnetAPI\GetTransactionDetailsRequest();

        $transactionId = $this->subjectReader->readTransactionId($subject);

        $paymentDO = $this->subjectReader->readPayment($subject);
        $payment = $paymentDO->getPayment();
        
        $anetRequest
            ->setTransId(
                $transactionId
            )->setMerchantAuthentication(
                $this->prepareMerchantAuthentication($payment->getMethodInstance())
            );

        return ['request' => $anetRequest];
    }
}
