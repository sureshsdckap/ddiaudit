<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload\Handler;

class VoidHandler implements HandlerInterface
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader
     */
    protected $subjectReader;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder
     */
    protected $transactionFinder;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * RefundHandler constructor.
     *
     * @param \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
     * @param \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader,
        \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
    
        $this->subjectReader = $subjectReader;
        $this->transactionFinder = $transactionFinder;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->orderRepository = $orderRepository;
    }


    /**
     * Handles void transaction
     *
     * @param array $subject
     * @return \Magento\Framework\Phrase|mixed
     * @throws \Exception
     */
    public function handle(array $subject)
    {
        $payloadDO = $this->subjectReader->readPayload($subject);

        $order = $payloadDO->getOrder();
        $payload = $payloadDO->getPayload();

        $txnId = $payload->getPayload()['id'];

        if (!$order || !$order->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Order doesn\'t exist.'));
        }

        if (!$order->canVoidPayment()) {
            throw new \Exception(
                __('Cannot void order #' . $order->getIncrementId())
            );
        }

        if ($this->transactionFinder->getTransaction($txnId . \AuthorizeNet\Core\Gateway\Config\Config::TRANS_SUFFIX_VOID)->getTransactionId()) {
            throw new \Exception(
                __('Transaction with the same id already exists.')
            );
        }

        $order->getPayment()->setSkipGatewayCommand(true);
        $order->getPayment()->void(new \Magento\Framework\DataObject());

        $this->orderRepository->save($order);

        return __('Voided order #%1', $order->getIncrementId());
    }
}
