<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload\Handler;

class RefundHandler implements HandlerInterface
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader
     */
    protected $subjectReader;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Payment\Gateway\CommandInterface
     */
    protected $command;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder
     */
    protected $transactionFinder;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;
    /**
     * @var \AuthorizeNet\Webhooks\Model\EmailSender
     */
    protected $emailSender;
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $creditmemoLoader;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory
     */
    protected $paymentFactory;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * RefundHandler constructor.
     *
     * @param \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Payment\Gateway\CommandInterface $command
     * @param \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \AuthorizeNet\Webhooks\Model\EmailSender $emailSender
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Sales\Model\Order\PaymentFactory $paymentFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Payment\Gateway\CommandInterface $command,
        \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        \AuthorizeNet\Webhooks\Model\EmailSender $emailSender,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Sales\Model\Order\PaymentFactory $paymentFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory
    ) {
    
        $this->subjectReader = $subjectReader;
        $this->invoiceService = $invoiceService;
        $this->objectManager = $objectManager;
        $this->command = $command;
        $this->transactionFinder = $transactionFinder;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->emailSender = $emailSender;
        $this->orderRepository = $orderRepository;
        $this->creditmemoLoader = $creditmemoLoader;
        $this->paymentFactory = $paymentFactory;
        $this->orderFactory = $orderFactory;
    }


    /**
     * Handles Refund of orders
     *
     * @param array $subject
     * @return \Magento\Framework\Phrase|mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function handle(array $subject)
    {
        $payloadDO = $this->subjectReader->readPayload($subject);

        $payload = $payloadDO->getPayload();

        $txnId = $payload->getPayload()['id'];

        if ($this->transactionFinder->getTransaction($txnId)->getTransactionId()) {
            throw new \Exception(
                __('Transaction with the same id already exists.')
            );
        }

        $payment = $this->getPaymentStub();
        $transactionDetails = $this->command->execute([
            'payment' => $this->paymentDataObjectFactory->create($payment),
            'transactionId' => $txnId,
            'resultAsObject' => true
        ]);

        $id = $transactionDetails->getRefTransId();
        $transaction = $this->transactionFinder->getTransaction($id);

        $order = $this->orderRepository->get($transaction->getOrderId());

        if (!$order->getPayment()->canRefund()) {
            throw new \Exception(
                __('Cannot create refund for order# ' . $order->getIncrementId())
            );
        }

        $amountToRefund = $transactionDetails->getAuthAmount();

        foreach ($order->getInvoiceCollection() as $invoice) {
            if (!in_array(
                $invoice->getTransactionId(),
                [$id, $id . \AuthorizeNet\Core\Gateway\Config\Config::TRANS_SUFFIX_CAPTURE]
            )) {
                continue;
            }

            if ($amountToRefund < $invoice->getBaseGrandTotal()) {
                $this->emailSender->send([
                    'type' => 'refund',
                    'amount' => $amountToRefund,
                    'total' => $invoice->getBaseGrandTotal(),
                    'order' => $order->getIncrementId(),
                    'transaction' => $id,
                ]);
            } else {
                $this->creditmemoLoader->setOrderId($order->getId());
                $this->creditmemoLoader->setInvoiceId($invoice->getId());
                $creditmemo = $this->creditmemoLoader->load();

                if (!$creditmemo) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Credit memo for order# %1 not found.', $order->getIncrementId())
                    );
                }

                if (!$creditmemo->isValidGrandTotal()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The credit memo\'s total must be positive.')
                    );
                }

                $creditmemo->addComment(
                    'Refunded ' . $order->getPayment()->formatPrice($amountToRefund) . ' from Authorize.Net. Transaction ID: "' . $txnId . ' " '
                );

                $creditmemoManagement = $this->objectManager->create(
                    \Magento\Sales\Api\CreditmemoManagementInterface::class
                );
                $creditmemo->getOrder()->getPayment()->setSkipGatewayCommand(true);
                $creditmemo->getOrder()->getPayment()->setTransactionId($txnId);
                $creditmemo->getOrder()->getPayment()->setLastTransId($txnId);
                $creditmemoManagement->refund($creditmemo);
            }
        }
        return __('Refunded amount %1 on order #%2', $amountToRefund, $order->getIncrementId());
    }

    /**
     * Get Payment Stub Record
     *
     * @return array $payment
     */
    private function getPaymentStub()
    {

        $payment = $this->paymentFactory->create();
        $payment->setMethod(\AuthorizeNet\Webhooks\Model\Payment\Webhook::METHOD_CODE);
        $order = $this->orderFactory->create();
        $order->setPayment($payment);

        return $payment;
    }
}
