<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use net\authorize\api\contract\v1 as AnetAPI;

class TransactionRequestBuilder extends \AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder implements BuilderInterface
{

    /**
     * @var $transactionType
     */
    protected $transactionType;

    /**
     * Builds request
     *
     * Update the transaction request type, merchant authentication and reference id to transaction request and send to Anet.
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();
        $opaqueData = new AnetAPI\OpaqueDataType();
        $paymentRequestType = new AnetAPI\PaymentType();
        
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        $amount = $this->subjectReader->readAmount($buildSubject);

        $opaqueData
            ->setDataKey($payment->getAdditionalInformation('encKey'))
            ->setDataValue($payment->getAdditionalInformation('encPaymentData'))
            ->setDataDescriptor(\AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::VC_DATA_DESCRIPTOR);

        $paymentRequestType
            ->setOpaqueData($opaqueData);

        $customerData = $this->prepareCustomerData($order->getCustomerId(), $order->getBillingAddress()->getEmail());

        $transactionRequestType
            ->setAmount($amount)
            ->setTransactionType($this->transactionType)
            ->setCustomerIP($order->getRemoteIp())
            ->setCallId($payment->getAdditionalInformation('callId'))
            ->setPayment($paymentRequestType)
            ->setCustomer($customerData)
            ->setTax($this->getTax($payment))
            ->setShipping($this->getShipping($payment))
            ->setOrder($this->prepareOrderData($order->getOrderIncrementId()))
            ->setBillTo($this->prepareAddressData($order->getBillingAddress()))
            ->setLineItems($this->prepareLineItems($order->getItems()))
            ->setCurrencyCode($order->getCurrencyCode());

        if ($address = $order->getShippingAddress()) {
            $transactionRequestType->setShipTo(
                $this->prepareAddressData($address, true)
            );
        }

        if ($solutionId = $this->prepareSolutionId($payment->getMethodInstance())) {
            $transactionRequestType->setSolution($solutionId);
        }
            
        $anetRequest
            ->setTransactionRequest($transactionRequestType)
            ->setMerchantAuthentication($this->prepareMerchantAuthentication($payment->getMethodInstance()))
            ->setRefId($this->generateRefId($order->getOrderIncrementId()));

        return ['request' => $anetRequest];
    }
}
