<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\ApproveFraudHandler;
use PHPUnit\Framework\TestCase;

class ApproveFraudHandlerTest extends TestCase
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
     * @var ApproveFraudHandler
     */
    protected $handler;
    /**
     * @var array
     */
    protected $subject;

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
            ->setMethods(['setSkipGatewayCommand','accept'])
            ->getMock();
        $this->subject = ['payload' => 'object'];
        $this->subjectReaderMock->expects(static::any())
            ->method('readPayload')
            ->with($this->subject)
            ->willReturn($this->payloadDOMock);
        $this->payloadDOMock->expects(static::any())
            ->method('getPayload')
            ->willReturn($this->payloadMock);

        $this->handler = new ApproveFraudHandler(
            $this->subjectReaderMock
        );
    }

    public function testHandle()
    {
        $id = 10002;
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::any())
            ->method('canReviewPayment')
            ->willReturn(true);
        $this->orderMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        $this->paymentMock->expects(static::once())
            ->method('accept')
            ->willReturnSelf();
        $this->paymentMock->expects(static::once())
            ->method('setSkipGatewayCommand')
            ->with(true);
        $this->orderMock->expects(static::once())
            ->method('save')
            ->willReturnSelf();
        $this->orderMock->expects(static::any())
            ->method('getIncrementId')
            ->willReturn($id);
        $this->assertEquals('Order #' . $id . ' approved', $this->handler->handle($this->subject));
    }

    public function testOrderException()
    {
        $id = 10001;
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturn(null);
        $this->payloadMock->expects(static::any())
            ->method('getPayload')
            ->willReturn(['id' => $id]);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to find appropriate order for transaction ' . $id);
        $this->handler->handle($this->subject);
    }

    public function testReviewPaymentException()
    {
        $id = 10002;
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::any())
            ->method('canReviewPayment')
            ->willReturn(false);
        $this->orderMock->expects(static::any())
            ->method('getIncrementId')
            ->willReturn($id);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot update order #' . $id);
        $this->handler->handle($this->subject);
    }
}
