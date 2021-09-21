<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\CaptureHandler;
use PHPUnit\Framework\TestCase;

class CaptureHandlerTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
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
     * @var \Magento\Sales\Model\Service\InvoiceService|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceServiceMock;
    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;
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
     * @var \Magento\Framework\DB\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionSaveMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\EmailSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSenderMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectMock;
    /**
     * @var CaptureHandlerTest
     */
    protected $handler;
    protected $incrementId;
    protected $orderId;
    protected $returnOrderMock;
    protected $baseTotalDue;
    protected $canInvoice;
    protected $invoiceTotalQty;
    protected $amountToCapture;
    protected $tnxId;

    protected function setUp()
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
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceServiceMock = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal','getTotalQty','getOrder','setRequestedCaptureCase','addComment','register'])
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
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSkipGatewayCommand','setIsTransactionClosed'])
            ->getMock();

        $this->txnId = 10001;
        $this->orderId = 1;
        $this->returnOrderMock = 1;
        $this->canInvoice = 1;
        $this->baseTotalDue = 100;
        $this->invoiceTotalQty = 10;
        $this->amountToCapture = 10;
        $this->subjectReaderMock->expects(static::any())
            ->method('readPayload')
            ->willReturn($this->payloadDOMock);
        $this->payloadDOMock->expects(static::any())
            ->method('getPayload')
            ->willReturn($this->payloadMock);
        $this->payloadMock->expects(static::any())
            ->method('getPayload')
            ->willReturn(['id' => $this->tnxId]);
        $this->transactionFinderMock->expects(static::any())
            ->method('getTransaction')
            ->with($this->tnxId . \AuthorizeNet\Core\Gateway\Config\Config::TRANS_SUFFIX_CAPTURE)
            ->willReturn($this->transactionMock);
        $this->incrementId = 10002;
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturnCallback([$this, 'getOrder']);
        $this->orderMock->expects(static::any())
            ->method('getId')
            ->willReturnCallback([$this, 'getOrderId']);
        $this->orderMock->expects(static::any())
            ->method('canInvoice')
            ->willReturnCallback([$this, 'canInvoice']);
        $this->orderMock->expects(static::any())
            ->method('getIncrementId')
            ->willReturn($this->incrementId);
        $this->orderMock->expects(static::any())
            ->method('getBaseTotalDue')
            ->willReturnCallback([$this, 'getBaseTotalDue']);
        $this->orderMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->commandMock->expects(static::any())
            ->method('execute')
            ->with([
                    'payment' => $this->paymentDataObjectMock,
                    'transactionId' => $this->tnxId,
                    'resultAsObject' => true
                ])
            ->willReturn($this->transactionDetailsMock);
        $this->paymentDataObjectFactoryMock->expects(static::any())
            ->method('create')
            ->with($this->paymentMock)
            ->willReturn($this->paymentDataObjectMock);
        $this->transactionDetailsMock->expects(static::any())
            ->method('getSettleAmount')
            ->willReturn($this->amountToCapture);
        $this->invoiceServiceMock->expects(static::any())
            ->method('prepareInvoice')
            ->with($this->orderMock)
            ->willReturn($this->invoiceMock);
        $this->invoiceMock->expects(static::any())
            ->method('getTotalQty')
            ->willReturnCallback([$this, 'getInvoiceTotalQty']);

        $this->handler = new CaptureHandler(
            $this->subjectReaderMock,
            $this->invoiceServiceMock,
            $this->objectManagerMock,
            $this->commandMock,
            $this->transactionFinderMock,
            $this->emailSenderMock,
            $this->paymentDataObjectFactoryMock
        );
    }

    public function testTransactionException()
    {
        $this->transactionMock->expects(static::any())
            ->method('getTransactionId')
            ->willReturn(10001);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Transaction with the same id already exists.');
        $this->handler->handle([]);
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testOrderException($value)
    {
        if ($value) {
            $this->orderId = 0;
        } else {
            $this->returnOrderMock = 0;
        }
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('Order doesn\'t exist.');
        $this->handler->handle([]);
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }

    public function testCanInvoiceException()
    {
        $this->canInvoice = 0;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The order# ' . $this->incrementId . ' does not allow an invoice to be created');
        $this->handler->handle([]);
    }

    public function testBaseTotalDueException()
    {
        $this->baseTotalDue = 0;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Capturing error for order #' . $this->incrementId);
        $this->handler->handle([]);
    }

    public function testInvoiceTotalQty()
    {
        $this->invoiceTotalQty = 0;
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->expectExceptionMessage('You can\'t create an invoice without products.');
        $this->handler->handle([]);
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
                'type' => 'capture',
                'amount' => $this->amountToCapture,
                'total' => $baseGrandTotal,
                'order' => $this->incrementId,
                'transaction' => $this->tnxId,
            ]);
        $this->assertEquals('Captured amount ' . $this->amountToCapture . ' on order #' . $this->incrementId, $this->handler->handle([]));
    }


    public function testHandle()
    {
        $this->invoiceMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->paymentMock->expects(static::any())
            ->method('setSkipGatewayCommand')
            ->with(true);
        $this->paymentMock->expects(static::any())
            ->method('setIsTransactionClosed')
            ->with(false);
        $this->invoiceMock->expects(static::any())
            ->method('setRequestedCaptureCase')
            ->with('online');
        $this->invoiceMock->expects(static::any())
            ->method('addComment')
            ->with('Invoice created from Authorize.Net')
            ->willReturnSelf();
        $this->invoiceMock->expects(static::any())
            ->method('register')
            ->willReturnSelf();
        $this->objectManagerMock->expects(static::any())
            ->method('create')
            ->with(\Magento\Framework\DB\Transaction::class)
            ->willReturn($this->transactionSaveMock);
        $this->transactionSaveMock->expects($this->at(0))
            ->method('addObject')
            ->with($this->invoiceMock)
            ->willReturnSelf();
        $this->transactionSaveMock->expects($this->at(1))
            ->method('addObject')
            ->with($this->orderMock)
            ->willReturnSelf();
        $this->transactionSaveMock->expects($this->once())
            ->method('save');
        $this->assertEquals('Captured amount ' . $this->amountToCapture . ' on order #' . $this->incrementId, $this->handler->handle([]));
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function getOrder()
    {
        if ($this->returnOrderMock) {
            return $this->orderMock;
        }
    }

    public function getBaseTotalDue()
    {
        return $this->baseTotalDue;
    }

    public function canInvoice()
    {
        return $this->canInvoice;
    }

    public function getInvoiceTotalQty()
    {
        return $this->invoiceTotalQty;
    }
}
