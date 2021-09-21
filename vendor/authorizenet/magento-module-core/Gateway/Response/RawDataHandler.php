<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;

class RawDataHandler implements \Magento\Payment\Gateway\Response\HandlerInterface
{

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \AuthorizeNet\Core\Model\Logger
     */
    protected $logger;

    /**
     * RawDataHandler Constructor
     *
     * @param SubjectReader                                    $subjectReader
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \AuthorizeNet\Core\Model\Logger                  $logger
     */
    public function __construct(
        SubjectReader $subjectReader,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \AuthorizeNet\Core\Model\Logger $logger
    ) {
        $this->subjectReader = $subjectReader;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Hanlde response
     *
     * Manage response to a transaction and update payment additional information details.
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transactionResponse = $this->subjectReader->readTransactionResponseObject($response);

        $payment = $paymentDO->getPayment();

        try {
            $methodInstance = $payment->getMethodInstance();

            $data = $methodInstance->fetchTransactionInfo(
                $payment,
                $transactionResponse->getTransactionResponse()->getTransId()
            );

            if ($payment instanceof \Magento\Sales\Model\Order\Payment) {
                $payment->setTransactionAdditionalInfo(
                    \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS,
                    $this->serializeArrays($data)
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->alert('Unable to get raw transaction data. Error was:' . $e->getLogMessage());
            if ($payment instanceof \Magento\Sales\Model\Order\Payment) {
                $payment->getOrder()->addStatusHistoryComment(
                    __('Please enable the transaction details API within the authorise.net portal to see additional '
                        . ' transaction details. See the Authorize.Net for magento 2 user manual for more information')
                );
            }
        }
    }

    /**
     * Set serialize array
     *
     * @param  array $array
     * @return array
     */
    private function serializeArrays($array)
    {
        return array_map(function ($element) {
            if (is_array($element)) {
                return $this->serializer->serialize($element);
            }
            return $element;
        }, $array);
    }
}
