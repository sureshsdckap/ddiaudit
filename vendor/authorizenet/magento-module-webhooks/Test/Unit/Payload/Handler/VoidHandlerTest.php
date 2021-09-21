<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\VoidHandler;
use PHPUnit\Framework\TestCase;

class VoidHandlerTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFinderMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;
    /**
     * @var \Magento\Sales\Model\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;
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
     * @var VoidHandler
     */
    protected $handler;

    protected $subject;
    protected $txnId;
    protected $canVoidPayment;
    protected $incrementId;


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
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->setMethods(['setSkipGatewayCommand', 'void'])
            ->getMock();
        $this->transactionFinderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\TransactionFinder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Model\OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subject = ['payload' => 'object'];
        $this->txnId = 10001;
        $this->canVoidPayment = true;

        $this->subjectReaderMock->expects(static::any())
            ->method('readPayload')
            ->with($this->subject)
            ->willReturn($this->payloadDOMock);
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->payloadDOMock->expects(static::any())
            ->method('getPayload')
            ->willReturn($this->payloadMock);
        $this->payloadMock->expects(static::any())
            ->method('getPayload')
            ->willReturn(['id' => $this->txnId]);
        $this->orderMock->expects($this->any())
            ->method('canVoidPayment')
            ->willReturnCallback([$this, 'canVoidPayment']);
        $this->orderMock->expects($this->any())
            ->method('getIncrementId')
            ->willReturn($this->incrementId);
        $this->orderMock->expects($this->any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->transactionFinderMock->expects(static::any())
            ->method('getTransaction')
            ->willReturn($this->transactionMock);

        $this->handler = new VoidHandler(
            $this->subjectReaderMock,
            $this->transactionFinderMock,
            $this->paymentDataObjectFactoryMock,
            $this->orderRepositoryMock
        );
    }

    public function testCanVoidPaymentException()
    {
        $this->canVoidPayment = false;
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot void order #' . $this->incrementId);
        $this->handler->handle($this->subject);
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

    public function testHandler()
    {
        $this->paymentMock->expects($this->any())
            ->method('setSkipGatewayCommand')
            ->with(true);
        $this->paymentMock->expects($this->any())
            ->method('void')
            ->with(new \Magento\Framework\DataObject);
        $this->orderRepositoryMock->expects($this->any())
            ->method('save')
            ->with($this->orderMock);
        $this->assertEquals('Voided order #' . $this->incrementId, $this->handler->handle($this->subject));
    }

    public function canVoidPayment()
    {
        return $this->canVoidPayment;
    }
}
