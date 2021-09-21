<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\CaptureWithApproveHandler;
use PHPUnit\Framework\TestCase;

class CaptureWithApproveHandlerTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Handler\HandlerPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerPoolMock;
    /**
     * @var \\AuthorizeNet\Webhooks\Payload\Handler\HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadDOMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var CaptureWithApproveHandler
     */
    protected $handler;

    protected $subject;

    protected function setUp()
    {
        $this->handlerPoolMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Handler\HandlerPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handlerMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Handler\HandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadDOMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Helper\SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->subject = ['payload' => 'object'];
        $this->subjectReaderMock->expects(static::any())
            ->method('readPayload')
            ->with($this->subject)
            ->willReturn($this->payloadDOMock);
        $this->handlerPoolMock->expects(static::any())
            ->method('get')
            ->willReturn($this->handlerMock);
        $this->handler = new CaptureWithApproveHandler(
            $this->subjectReaderMock,
            $this->handlerPoolMock
        );
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testHandle($value)
    {
        $fraudResult = 'fraudResult';
        $result = 'handlerResult';
        $index = 0;
        $expected = [];
        $orderId = '101';
        $this->payloadDOMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->orderMock->expects(static::any())
            ->method('getId')
            ->willReturn($orderId);
        $this->orderMock->expects(static::any())
            ->method('canReviewPayment')
            ->willReturn($value);
        if ($value) {
            $this->handlerPoolMock->expects($this->at($index))
                ->method('get')
                ->with('net.authorize.payment.fraud.approved');
            $this->handlerMock->expects($this->at($index))
                ->method('handle')
                ->with($this->subject)
                ->willReturn($fraudResult);
            $expected[] = $fraudResult;
            $index++;
        }
        $this->handlerPoolMock->expects($this->at($index))
            ->method('get')
            ->with('capture_only');
        $this->handlerMock->expects($this->at($index))
            ->method('handle')
            ->with($this->subject)
            ->willReturn($result);
        $expected[] = $result;

        $this->assertEquals(implode(PHP_EOL, $expected), $this->handler->handle($this->subject));
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testOrderException($value)
    {
        if ($value) {
            $this->payloadDOMock->expects(static::any())
                ->method('getOrder')
                ->willReturn(null);
        } else {
            $this->payloadDOMock->expects(static::any())
                ->method('getOrder')
                ->willReturn($this->orderMock);
            $this->orderMock->expects(static::any())
                ->method('getId')
                ->willReturn(null);
        }
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);
        $this->expectExceptionMessage('Order doesn\'t exist.');
        $this->handler->handle($this->subject);
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
}
