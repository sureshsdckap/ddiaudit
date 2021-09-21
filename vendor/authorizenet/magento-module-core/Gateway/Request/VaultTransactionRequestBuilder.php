<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use Magento\Sales\Model\Order\Payment;

use net\authorize\api\contract\v1 as AnetAPI;

class VaultTransactionRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Build request to get Vault Transaction
     *
     * @param array $commandSubject
     * @return array
     * @throws \Exception
     */
    public function build(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var Payment $payment */
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
            )->setProfile(
                $this->prepareCustomerProfile($payment)
            )->setTax(
                $this->getTax($payment)
            )->setShipping(
                $this->getShipping($payment)
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
     * Build request to Prepare Customer Profile from Vault Payment token
     *
     * @param Payment $payment
     * @return AnetAPI\CustomerProfilePaymentType
     * @throws \Exception
     */
    private function prepareCustomerProfile(Payment $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();

        /** @var \Magento\Vault\Model\PaymentToken $paymentToken */
        $paymentToken = $extensionAttributes->getVaultPaymentToken();

        $tokenParts = explode(':', $paymentToken->getGatewayToken());
        if (count($tokenParts) < 2) {
            throw new \Exception('Invalid gateway token format');
        }

        $customerProfilePaymentType = new AnetAPI\CustomerProfilePaymentType();
        $paymentProfilePaymentType = new AnetAPI\PaymentProfileType();

        $paymentProfilePaymentType->setPaymentProfileId($tokenParts[1]);

        return $customerProfilePaymentType
            ->setCustomerProfileId(
                $tokenParts[0]
            )->setPaymentProfile(
                $paymentProfilePaymentType
            );
    }
}
