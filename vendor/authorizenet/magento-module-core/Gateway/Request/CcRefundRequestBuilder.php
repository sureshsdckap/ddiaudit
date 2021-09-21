<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use Magento\Sales\Model\Order\Payment;
use net\authorize\api\contract\v1 as AnetAPI;

class CcRefundRequestBuilder extends AbstractRequestBuilder
{
    
    const CC_EXP_DATE_MASKED = 'XXXX';
    
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Reader $reader,
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader,
        $transactionType = \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_REFUND
    ) {
        parent::__construct($reader, $subjectReader, $transactionType);
    }

    /**
     * Build request for refund order transaction.
     *
     * @param  array $commandSubject
     * @return array $request
     */
    public function build(array $commandSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $orderAdapter = $paymentDO->getOrder();

        $amount = $this->subjectReader->readAmount($commandSubject);

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $paymentRequestType = new AnetAPI\PaymentType();
        $ccInfo = new AnetAPI\CreditCardType();

        $ccInfo
            ->setCardNumber($payment->getCcLast4())
            ->setExpirationDate(self::CC_EXP_DATE_MASKED);

        $paymentRequestType->setCreditCard($ccInfo);

        $transactionRequestType
            ->setTransactionType(
                $this->getTransactionType()
            )->setRefTransId(
                $this->prepareParentTransactionId($payment->getParentTransactionId())
            )->setAmount(
                $this->formatPrice($amount)
            )->setOrder(
                $this->prepareOrderData(
                    $orderAdapter->getOrderIncrementId()
                )
            )->setPayment(
                $paymentRequestType
            )->setCurrencyCode(
                $orderAdapter->getCurrencyCode()
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
                    $orderAdapter->getOrderIncrementId()
                )
            );

        return ['request' => $anetRequest];
    }
}
