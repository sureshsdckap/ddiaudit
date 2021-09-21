<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionIdHandler
 * @package AuthorizeNet\Core\Gateway\Response
 */
class CcInfoHandler implements HandlerInterface
{
    const KEY_CARD_TYPE = 'cardType';
    const KEY_CARD_NUMBER = 'cardNumber';
    const KEY_AVS_RESULT_CODE = 'avsResultCode';
    const KEY_AUTH_CODE = 'authCode';
    const KEY_CVV_RESULT_CODE = 'cvvResultCode';
    const KEY_CAVV_RESULT_CODE = 'cavvResultCode';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * CcInfoHandler Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handle response
     *
     * Manage Credit card information from Transaction response
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment Payment */
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $responseObject = $this->subjectReader->readTransactionResponseObject($response);
        $transactionDetails = $responseObject->getTransactionResponse();

        //TODO: check what else could be handled here
        $payment->setCcLast4(substr($transactionDetails->getAccountNumber(), -4, 4));
        $payment->setCcAvsStatus($transactionDetails->getAvsResultCode());

        $payment->setAdditionalInformation(self::KEY_CARD_TYPE, $transactionDetails->getAccountType());
        $payment->setAdditionalInformation(self::KEY_CARD_NUMBER, $transactionDetails->getAccountNumber());
        $payment->setAdditionalInformation(self::KEY_AVS_RESULT_CODE, $transactionDetails->getAvsResultCode());
        $payment->setAdditionalInformation(self::KEY_AUTH_CODE, $transactionDetails->getAuthCode());
        $payment->setAdditionalInformation(self::KEY_CVV_RESULT_CODE, $transactionDetails->getCvvResultCode());
        $payment->setAdditionalInformation(self::KEY_CAVV_RESULT_CODE, $transactionDetails->getCavvResultCode());
    }
}
