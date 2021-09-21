<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use net\authorize\api\contract\v1 as AnetAPI;

class SettleRequestBuilder extends AbstractRequestBuilder
{
    /**
     * Build request for settle amount
     *
     * @param array $commandSubject
     * @return array
     */
    public function build(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        $order = $paymentDO->getOrder();
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $transactionRequestType
            ->setTransactionType(
                $this->getTransactionType()
            )->setRefTransId(
                $this->prepareParentTransactionId(
                    $payment->getParentTransactionId()
                )
            )->setCurrencyCode(
                $order->getCurrencyCode()
            );

        try {
            $amount = $this->subjectReader->readAmount($commandSubject);
            $transactionRequestType
                ->setAmount(
                    $this->formatPrice($amount)
                );
        } catch (\InvalidArgumentException $e) {
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
