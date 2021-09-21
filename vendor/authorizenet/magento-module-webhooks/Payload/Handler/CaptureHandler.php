<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

class CaptureHandler implements HandlerInterface
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
     * @var \AuthorizeNet\Webhooks\Model\EmailSender
     */
    protected $emailSender;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;

    /**
     * CaptureHandler constructor.
     *
     * @param \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
     * @param \Magento\Sales\Model\Service\InvoiceService $invoiceService
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Payment\Gateway\CommandInterface $command
     * @param \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder
     * @param \AuthorizeNet\Webhooks\Model\EmailSender $emailSender
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Payment\Gateway\CommandInterface $command,
        \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder,
        \AuthorizeNet\Webhooks\Model\EmailSender $emailSender,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
    ) {
    
        $this->subjectReader = $subjectReader;
        $this->invoiceService = $invoiceService;
        $this->objectManager = $objectManager;
        $this->command = $command;
        $this->transactionFinder = $transactionFinder;
        $this->emailSender = $emailSender;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }


    /**
     * Captured order amount and generate the invoice
     *
     * @param array $subject
     * @return \Magento\Framework\Phrase|mixed
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function handle(array $subject)
    {
        $payloadDO = $this->subjectReader->readPayload($subject);

        $order = $payloadDO->getOrder();
        $payload = $payloadDO->getPayload();

        $txnId = $payload->getPayload()['id'];

        if ($this->transactionFinder->getTransaction($txnId . \AuthorizeNet\Core\Gateway\Config\Config::TRANS_SUFFIX_CAPTURE)->getTransactionId()) {
            throw new \Exception(
                __('Transaction with the same id already exists.')
            );
        }

        if (!$order || !$order->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Order doesn\'t exist.'));
        }

        if (!$order->canInvoice()) {
            throw new \Exception(
                __('The order# %1 does not allow an invoice to be created', $order->getIncrementId())
            );
        }

        if ($order->getBaseTotalDue() <= 0) {
            throw new \Exception(
                __('Capturing error for order #' . $order->getIncrementId())
            );
        }

        $transactionDetails = $this->command->execute([
            'payment' => $this->paymentDataObjectFactory->create($order->getPayment()),
            'transactionId' => $txnId,
            'resultAsObject' => true]);
        $amountToCapture = $transactionDetails->getSettleAmount();

        $invoice = $this->invoiceService->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can\'t create an invoice without products.')
            );
        }

        if ($amountToCapture < $invoice->getBaseGrandTotal()) {
            $this->emailSender->send([
                'type' => 'capture',
                'amount' => $amountToCapture,
                'total' => $invoice->getBaseGrandTotal(),
                'order' => $order->getIncrementId(),
                'transaction' => $txnId,
            ]);
        } else {
            $invoice->getOrder()->getPayment()->setSkipGatewayCommand(true);
            $invoice->getOrder()->getPayment()->setIsTransactionClosed(false);

            $invoice->setRequestedCaptureCase($invoice::CAPTURE_ONLINE);

            $invoice
                ->addComment('Invoice created from Authorize.Net')
                ->register();

            $transactionSave = $this->objectManager->create(
                \Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
        }
        return __('Captured amount %1 on order #%2', $amountToCapture, $order->getIncrementId());
    }
}
