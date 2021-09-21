<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Gateway\Request;

use AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder;
use Magento\Sales\Model\Order\Payment;

use net\authorize\api\contract\v1 as AnetAPI;

class RefundRequestBuilder extends AbstractRequestBuilder
{
    const ECHECK_MASK = 'XXXX';

    /**
     * Build request of transaction data
     *
     * Create request for Anet API using order transaction data.
     *
     * @param array $commandSubject
     * @return array
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
            ->setRefTransId(
                $payment->getParentTransactionId()
            )->setTransactionType(
                $this->getTransactionType()
            )->setAmount(
                $this->formatPrice($amount)
            )->setPayment(
                $this->preparePayment($commandSubject)
            )->setCurrencyCode(
                $order->getCurrencyCode()
            );

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
     * Prepare for payment
     *
     * Set Anet payment object using Anet bank account info.
     *
     * @param array $subject
     * @return AnetAPI\PaymentType
     */
    protected function preparePayment($subject)
    {
        $anetPayment = new AnetAPI\PaymentType();
        $anetBankAccount = new AnetAPI\BankAccountType();

        $anetBankAccount
            ->setRoutingNumber(
                self::ECHECK_MASK . $this->subjectReader->readECheckRoutingNumber($subject)
            )->setAccountNumber(
                self::ECHECK_MASK . $this->subjectReader->readECheckAccountNumber($subject)
            )->setNameOnAccount(
                $this->subjectReader->readECheckNameOnAccount($subject)
            )->setAccountType(
                $this->subjectReader->readECheckAccountType($subject)
            )->setEcheckType(
                'PPD'  // @TODO: move to settings?
            );

        $anetPayment->setBankAccount($anetBankAccount);

        return $anetPayment;
    }
}
