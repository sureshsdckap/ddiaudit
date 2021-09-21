<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\RefundHandler;
use PHPUnit\Framework\TestCase;

class RefundHandlerTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceServiceMock;
    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceCollectionMock;
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;
    /**
     * @var \Magento\Payment\Gateway\CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandMock;
    /**
     * @var \net\authorize\api\contract\v1\TransactionDetailsType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionDetailsMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFinderMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadDOMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\EmailSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSenderMock;
    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoLoaderMock;
    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;
    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoManagementMock;
    /**
     * @var \Magento\Sales\Model\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;
    /**
     * @var \Magento\Sales\Model\Order\PaymentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentFactoryMock;
    /**
     * @var \Magento\Sales\Model\OrderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderFactoryMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectMock;
    /**
     * @var RefundHandler
     */
    protected $handler;

    protected $incrementId;
    protected $orderId;
    protected $canRefund;
    protected $subject;
    protected $txnId;
    protected $amountToRefund;
    protected $returnCreditmemoMock;
    protected $transactionId;
    protected $validGrandTotal;
    protected $invoiceTransactionId;

    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Helper\SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadDOMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Api\PayloadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Model\OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoLoaderMock = $this->getMockBuilder(\Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader::class)
            ->disableOriginalConstructor()
            ->setMethods(['setOrderId', 'setInvoiceId', 'load'])
            ->getMock();
        $this->invoiceServiceMock = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal', 'getTransactionId', 'getId'])
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->commandMock = $this->getMockBuilder(\Magento\Payment\Gateway\CommandInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionDetailsMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionDetailsType::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFinderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\TransactionFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionSaveMock = $this->getMockBuilder(\Magento\Framework\DB\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->emailSenderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\EmailSender::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentFactoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\PaymentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setMethod','canRefund','formatPrice','setTransactionId','setLastTransId','setSkipGatewayCommand'])
            ->getMock();
        $this->invoiceCollectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoManagementMock = $this->getMockBuilder(\Magento\Sales\Api\CreditmemoManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->txnId = 10001;
        $this->transactionId = 20001;
        $this->incrementId = 1111;
        $this->canRefund = true;
        $this->amountToRefund = 10;
        $this->invoiceTransactionId = $this->transactionId;
        $this->returnCreditmemoMock = true;
        $this->validGrandTotal = true;
        $orderId = 30001;
        $invoiceId = 30002;
        $this->subject = ['payload' => 'object'];
        $invoices = [
            $this->invoiceMock
        ];

        $this->subjectReaderMock->expects(static::any())
            ->method('readPayload')
            ->with($this->subject)
            ->willReturn($this->payloadDOMock);
        $this->payloadDOMock->expects(static::any())
            ->method('getPayload')
            ->willReturn($this->payloadMock);
        $this->payloadMock->expects(static::any())
            ->method('getPayload')
            ->willReturn(['id' => $this->txnId]);
        $this->transactionFinderMock->expects(static::any())
            ->method('getTransaction')
            ->willReturn($this->transactionMock);
        $this->paymentFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->paymentMock);
        $this->paymentMock->expects(static::any())
            ->method('setMethod')
            ->with(\AuthorizeNet\Webhooks\Model\Payment\Webhook::METHOD_CODE);
        $this->orderFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->orderMock);
        $this->paymentDataObjectFactoryMock->expects(static::any())
            ->method('create')
            ->with($this->paymentMock)
            ->willReturn($this->paymentDataObjectMock);
        $this->commandMock->expects(static::any())
            ->method('execute')
            ->with([
                    'payment' => $this->paymentDataObjectMock,
                    'transactionId' => $this->txnId,
                    'resultAsObject' => true
                ])
            ->willReturn($this->transactionDetailsMock);
        $this->transactionDetailsMock->expects(static::any())
            ->method('getRefTransId')
            ->willReturn($this->transactionId);
        $this->transactionDetailsMock->expects(static::any())
            ->method('getAuthAmount')
            ->willReturn($this->amountToRefund);
        $this->transactionMock->expects(static::any())
            ->method('getOrderId')
            ->willReturn($orderId);
        $this->orderRepositoryMock->expects(static::any())
            ->method('get')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->orderMock->expects(static::any())
            ->method('getIncrementId')
            ->willReturn($this->incrementId);
        $this->orderMock->expects(static::any())
            ->method('getInvoiceCollection')
            ->willReturn($this->invoiceCollectionMock);
        $this->orderMock->expects(static::any())
            ->method('getId')
            ->willReturn($orderId);
        $this->paymentMock->expects(static::any())
            ->method('canRefund')
            ->willReturnCallback([$this, 'canRefund']);
        $this->invoiceCollectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($invoices));
        $this->invoiceMock->expects($this->any())
            ->method('getTransactionId')
            ->willReturnCallback([$this, 'getInvoiceTransactionId']);
        $this->invoiceMock->expects(static::any())
            ->method('getId')
            ->willReturn($invoiceId);
        $this->creditmemoLoaderMock->expects($this->any())
            ->method('setOrderId')
            ->with($orderId);
        $this->creditmemoLoaderMock->expects($this->any())
            ->method('setInvoiceId')
            ->with($invoiceId);
        $this->creditmemoLoaderMock->expects($this->any())
            ->method('load')
            ->willReturnCallback([$this, 'getCreditmemo']);
        $this->creditmemoMock->expects($this->any())
            ->method('isValidGrandTotal')
            ->willReturnCallback([$this, 'isValidGrandTotal']);

        $this->handler = new RefundHandler(
            $this->subjectReaderMock,
            $this->invoiceServiceMock,
            $this->objectManagerMock,
            $this->commandMock,
            $this->transactionFinderMock,
            $this->paymentDataObjectFactoryMock,
            $this->emailSenderMock,
            $this->orderRepositoryMock,
            $this->creditmemoLoaderMock,
            $this->paymentFactoryMock,
            $this->orderFactoryMock
        );
    }

    public function testTransactionException()
    {
        $this->transactionMock->expects(static::any())
            ->method('getTransactionId')
            ->willReturn(1);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction with the same id already exists.');
        $this->handler->handle($this->subject);
    }

    public function testRefundException()
    {
        $this->canRefund = false;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot create refund for order# ' . $this->incrementId);
        $this->handler->handle($this->subject);
    }

    public function testInvoiceTransactionId()
    {
        $this->invoiceTransactionId = 0;
        $this->assertEquals('Refunded amount ' . $this->amountToRefund . ' on order #' . $this->incrementId, $this->handler->handle($this->subject));
    }

    public function testSendEmail()
    {
        $baseGrandTotal = 11;
        $this->invoiceMock->expects(static::any())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);
        $this->emailSenderMock->expects(static::any())
            ->method('send')
            ->with([
                'type' => 'refund',
                'amount' => $this->amountToRefund,
                'total' => $baseGrandTotal,
                'order' => $this->incrementId,
                'transaction' => $this->transactionId,
            ]);
        $this->assertEquals('Refunded amount ' . $this->amountToRefund . ' on order #' . $this->incrementId, $this->handler->handle($this->subject));
    }

    public function testCredimemoException()
    {
        $this->returnCreditmemoMock = false;
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('Credit memo for order# ' . $this->incrementId . ' not found.');
        $this->handler->handle($this->subject);
    }

    public function testCredimemoValidGrandTotalException()
    {
        $this->validGrandTotal = false;
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('The credit memo\'s total must be positive.');
        $this->handler->handle($this->subject);
    }

    public function testHandler()
    {
        $this->paymentMock->expects($this->any())
            ->method('formatPrice')
            ->with($this->amountToRefund)
            ->willReturn($this->amountToRefund);
        $this->creditmemoMock->expects($this->any())
            ->method('addComment')
            ->with('Refunded ' . $this->amountToRefund . ' from Authorize.Net. Transaction ID: "' . $this->txnId . ' " ')
            ->willReturnCallback([$this, 'isValidGrandTotal']);
        $this->objectManagerMock->expects(static::any())
            ->method('create')
            ->with(\Magento\Sales\Api\CreditmemoManagementInterface::class)
            ->willReturn($this->creditmemoManagementMock);
        $this->creditmemoMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->paymentMock->expects(static::once())
            ->method('setSkipGatewayCommand')
            ->with(true);
        $this->paymentMock->expects(static::once())
            ->method('setTransactionId')
            ->with($this->txnId);
        $this->paymentMock->expects(static::once())
            ->method('setLastTransId')
            ->with($this->txnId);
        $this->creditmemoManagementMock->expects($this->any())
            ->method('refund')
            ->with($this->creditmemoMock);
        $this->assertEquals('Refunded amount ' . $this->amountToRefund . ' on order #' . $this->incrementId, $this->handler->handle($this->subject));
    }

    public function canRefund()
    {
        return $this->canRefund;
    }

    public function getInvoiceTransactionId()
    {
        return $this->invoiceTransactionId;
    }

    public function getCreditmemo()
    {
        if ($this->returnCreditmemoMock) {
            return $this->creditmemoMock;
        }
    }

    public function isValidGrandTotal()
    {
        return $this->validGrandTotal;
    }
}
