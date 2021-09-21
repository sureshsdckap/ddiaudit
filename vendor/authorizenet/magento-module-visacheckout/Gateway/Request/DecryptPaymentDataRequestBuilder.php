<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Gateway\Request;

use net\authorize\api\contract\v1 as AnetAPI;

class DecryptPaymentDataRequestBuilder extends \AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder
{
    
    /**
     * DecryptPaymentDataRequestBuilder Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Config\Reader $reader
     * @param \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string $transactionType
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Reader $reader,
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($reader, $subjectReader, null);
    }

    /**
     * Update opaque data type and send request
     *
     * This method executes to generate an object of DecryptPaymentDataRequest and OpaqueDataType.
     * Update opaque and merchant authentication data and send a request to Anet.
     *
     * @param array $commandSubject
     * @return array
     */
    public function build(array $commandSubject)
    {
        $anetRequest = new AnetAPI\DecryptPaymentDataRequest();
        $opaqueData = new AnetAPI\OpaqueDataType();
        
        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        
        /** @var \Magento\Quote\Model\Quote\Payment $payment */
        $payment = $paymentDO->getPayment();

        $opaqueData->setDataDescriptor(\AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::VC_DATA_DESCRIPTOR);

        $opaqueData
            ->setDataKey($payment->getAdditionalInformation(\AuthorizeNet\VisaCheckout\Model\Checkout::PARAM_ENC_KEY))
            ->setDataValue($payment->getAdditionalInformation(\AuthorizeNet\VisaCheckout\Model\Checkout::PARAM_ENC_PAYMENT_DATA));
        
        $anetRequest
            ->setCallId($payment->getAdditionalInformation(\AuthorizeNet\VisaCheckout\Model\Checkout::PARAM_CALL_ID));

        $anetRequest
            ->setOpaqueData($opaqueData)
            ->setMerchantAuthentication($this->prepareMerchantAuthentication($payment->getMethodInstance()));

        return ['request' => $anetRequest];
    }
}
