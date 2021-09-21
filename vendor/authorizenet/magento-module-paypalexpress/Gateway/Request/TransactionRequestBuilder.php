<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Request;

use AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder;

use net\authorize\api\contract\v1 as AnetAPI;

class TransactionRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Prepare request from the Order data
     * Initialize transaction request
     *
     * @param array $commandSubject
     * @return array
     */
    public function build(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $transactionRequestType
            ->setRefTransId(
                $this->subjectReader->readPayPalInitTransId($commandSubject)
            )->setTransactionType(
                $this->getTransactionType()
            )->setPayment(
                $this->preparePayment(
                    $this->subjectReader->readPayPalPayerId($commandSubject)
                )
            )->setCurrencyCode(
                $order->getCurrencyCode()
            )->setTax(
                $this->getTax($payment)
            )->setCustomer(
                $this->prepareCustomerData(
                    $order->getCustomerId(),
                    $order->getBillingAddress()->getEmail()
                )
            )->setLineItems(
                $this->prepareLineItems($order->getItems())
            )->setOrder(
                $this->prepareOrderData($order->getOrderIncrementId())
            );

        if ($shipping = $this->getShipping($payment)) {
            $transactionRequestType->setShipping($shipping);
        }

        try {
            $amount = $this->subjectReader->readAmount($commandSubject);
            $transactionRequestType
                ->setAmount(
                    $this->formatPrice($amount)
                );
        } catch (\InvalidArgumentException $e) {
        }

        if ($address = $order->getShippingAddress()) {
            $transactionRequestType->setShipTo(
                $this->prepareAddressData($address, true)
            );
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

    /**
     * Retrieve Cart item list of checkout
     *
     * @param  array $item
     * @return array $item
     */
    protected function prepareLineItem($item)
    {

        $item = parent::prepareLineItem($item);

        //make sure taxable is set to null, PayPal doesn't like that
        $item->setTaxable(null);

        return $item;
    }

    /**
     * Set Payer id and PayPal Type in anetPayment request
     *
     * @param string $payerId
     * @return AnetAPI\PaymentType
     */
    protected function preparePayment($payerId)
    {
        $anetPayment = new AnetAPI\PaymentType();
        $anetPayPal = new AnetAPI\PayPalType();

        $anetPayPal->setPayerID($payerId);

        return $anetPayment->setPayPal($anetPayPal);
    }
}
