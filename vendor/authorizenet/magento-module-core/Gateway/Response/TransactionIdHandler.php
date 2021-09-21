<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use AuthorizeNet\Core\Gateway\Config\Config;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class TransactionIdHandler implements HandlerInterface
{
    const TRANSACTION_ID = 'transactionId';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $transactionIdSuffix = '';

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * TransactionIdHandler Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param string $transactionIdSuffix
     */
    public function __construct(
        Config $config,
        SubjectReader $subjectReader,
        $transactionIdSuffix = ''
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->transactionIdSuffix = $transactionIdSuffix;
    }

    /**
     * Hanlde response
     *
     * Update and set property transaction details
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment Payment */
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $responseObject = $this->subjectReader->readTransactionResponseObject($response);
        $transactionDetails = $responseObject->getTransactionResponse();

        if ($parentTransId = $transactionDetails->getRefTransID()) {
            $payment->setParentTransactionId($parentTransId);
        }

        $transactionId = $this->prepareTransactionId($transactionDetails->getTransId());
        
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);

        // do not overwrite initial transaction id
        if (!$payment->hasAdditionalInformation(self::TRANSACTION_ID)) {
            $payment->setAdditionalInformation(self::TRANSACTION_ID, $transactionId);
        }

        $payment->setIsTransactionClosed(
            $this->canCloseTransaction($payment)
        );
        $payment->setShouldCloseParentTransaction(
            $this->canCloseParentTransaction($payment)
        );
    }

    /**
     * Get trnsaction id
     *
     * @param string $transId
     * @return string
     */
    protected function prepareTransactionId($transId)
    {
        return $transId . $this->transactionIdSuffix;
    }

    /**
     * Check can close parent transaction or not.
     *
     * @param Payment $payment
     * @return bool
     */
    protected function canCloseParentTransaction($payment)
    {
        return false;
    }

    /**
     * Check Can close a transaction or not.
     *
     * @param Payment $payment
     * @return bool
     */
    protected function canCloseTransaction($payment)
    {
        return false;
    }
}
