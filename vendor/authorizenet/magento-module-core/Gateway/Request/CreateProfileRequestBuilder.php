<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use Magento\Sales\Model\Order\Payment;

use net\authorize\api\contract\v1 as AnetAPI;

class CreateProfileRequestBuilder extends AbstractRequestBuilder
{
    /**
     *  Build request to create customer profile in Anet
     *
     * @param array $subject
     * @return array
     */
    public function build(array $subject)
    {
        $paymentDO = $this->subjectReader->readPayment($subject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $order = $paymentDO->getOrder();

        $anetRequest = new AnetAPI\CreateCustomerProfileFromTransactionRequest();
        $customerProfileType = new AnetAPI\CustomerProfileBaseType();

        $customerProfileType->setMerchantCustomerId($order->getCustomerId());

        $anetRequest
            ->setTransId(
                $payment->getTransactionId()
            )->setCustomer(
                $customerProfileType
            )->setMerchantAuthentication(
                $this->prepareMerchantAuthentication($payment->getMethodInstance())
            );

        return ['request' => $anetRequest];
    }
}
