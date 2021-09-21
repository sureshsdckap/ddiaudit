<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use net\authorize\api\contract\v1 as AnetAPI;

class OpaqueDataTransactionRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Build request to get Opaque Data Transaction
     *
     * @param array $commandSubject
     * @return array
     */
    public function build(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        $payment = $paymentDO->getPayment();

        $order = $paymentDO->getOrder();

        $amount = $this->subjectReader->readAmount($commandSubject);

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $transactionRequestType
            ->setTransactionType(
                $this->getTransactionType()
            )->setAmount(
                $this->formatPrice($amount)
            )->setOrder(
                $this->prepareOrderData(
                    $order->getOrderIncrementId()
                )
            )->setPayment(
                $this->preparePaymentByNonce(
                    $this->subjectReader->readOpaqueData($commandSubject)
                )
            )->setTax(
                $this->getTax($payment)
            )->setShipping(
                $this->getShipping($payment)
            )->setBillTo(
                $this->prepareAddressData(
                    $order->getBillingAddress()
                )
            )->setCustomer(
                $this->prepareCustomerData(
                    $order->getCustomerId(),
                    $order->getBillingAddress()->getEmail()
                )
            )->setLineItems(
                $this->prepareLineItems($order->getItems())
            )->addToTransactionSettings(
                $this->prepareTransactionSettings()
            )->setCurrencyCode(
                $order->getCurrencyCode()
            );

        if ($address = $order->getShippingAddress()) {
            $transactionRequestType->setShipTo(
                $this->prepareAddressData($address, true)
            );
        }

        if ($cardholderAuthentication = $this->prepareCardholderAuthentication($payment)) {
            $transactionRequestType->setCardholderAuthentication($cardholderAuthentication);
        }

        if ($solutionId = $this->prepareSolutionId($payment->getMethodInstance())) {
            $transactionRequestType->setSolution($solutionId);
        }

        $anetRequest
            ->setTransactionRequest(
                $transactionRequestType
            )->setMerchantAuthentication(
                $this->prepareMerchantAuthentication($payment->getMethodInstance())
            )->setRefId(
                $this->generateRefId(
                    $order->getOrderIncrementId()
                )
            );

        return ['request' => $anetRequest];
    }
}
