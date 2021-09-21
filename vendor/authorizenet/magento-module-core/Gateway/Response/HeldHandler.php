<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

class HeldHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    const KEY_AFDS_ACTION = 'FDSFilterAction';
    const KEY_AFDS_FILTER_LIST = 'FDSFilters';

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serialize;

    /**
     * @var \AuthorizeNet\Core\Model\Logger
     */
    protected $logger;

    /**
     * HeldHandler Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Helper\SubjectReader  $subjectReader
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \AuthorizeNet\Core\Model\Logger                  $logger
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \AuthorizeNet\Core\Model\Logger $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->serialize = $serializer;
        $this->logger = $logger;
    }

    /**
     * Manage response of held transaction
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();

        $response = $this->subjectReader->readTransactionResponseObject($response);

        if ($response->getTransactionResponse()->getResponseCode() != \AuthorizeNet\Core\Gateway\Validator\TransactionResponseCodeValidator::TRANSACTION_CODE_HELD) {
            return;
        }

        if (!$payment instanceof \Magento\Sales\Model\Order\Payment) {
            return;
        }

        $payment
            ->setIsTransactionPending(true)
            ->setIsFraudDetected(true);

        $details = $this->getTransactionDetails($payment, $response->getTransactionResponse()->getTransId());

        if (!$details) {
            return;
        }

        if (isset($details[self::KEY_AFDS_ACTION])) {
            $payment->setAdditionalInformation(
                self::KEY_AFDS_ACTION,
                $this->prepareValue($details[self::KEY_AFDS_ACTION] ?? '')
            );
        }

        if (isset($details[self::KEY_AFDS_FILTER_LIST])) {
            $payment->setAdditionalInformation(
                self::KEY_AFDS_FILTER_LIST,
                $this->prepareValue($details[self::KEY_AFDS_FILTER_LIST] ?? '')
            );
        }
    }

    /**
     * Get Transaction Details of held transactions
     *
     * @param  \Magento\Sales\Model\Order\Payment $payment
     * @param  $transactionId
     * @return array $rawDetails
     */
    private function getTransactionDetails(\Magento\Sales\Model\Order\Payment $payment, $transactionId)
    {

        $rawDetails = [];

        $transactionData = $payment->getTransactionAdditionalInfo();

        try {
            $rawDetails = $transactionData[\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS]
                ?? $payment->getMethodInstance()->fetchTransactionInfo($payment, $transactionId)
                ?? [];
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->alert('Unable to fetch transaction details. Error was: ' . $e->getMessage());
        }

        return $rawDetails;
    }

    /**
     * Prepare of value
     *
     * @param  string $value
     * @return string $value
     */
    private function prepareValue($value)
    {

        try {
            if (is_string($value)) {
                $value = $this->serialize->unserialize($value);
            }
        } catch (\InvalidArgumentException $e) {
            //seems value is not serialized, just work with it
        }

        return $value;
    }
}
