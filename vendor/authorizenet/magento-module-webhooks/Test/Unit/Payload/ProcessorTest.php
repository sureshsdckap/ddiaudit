<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload;

use AuthorizeNet\Webhooks\Api\PayloadInterface;
use AuthorizeNet\Webhooks\Payload\Processor;
use PHPUnit\Framework\TestCase;

class ProcessorTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadDataObjectFactoryMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\PayloadDataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadDataObjectMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Handler\HandlerPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerPoolMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Handler\HandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $handlerMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Payload|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\ResourceModel\Payload|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadResourceMock;
    /**
     * @var Processor
     */
    protected $processor;
    protected $message;
    protected $eventType;

    protected function setUp()
    {
        $this->payloadDataObjectFactoryMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\PayloadDataObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadDataObjectMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\PayloadDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handlerPoolMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Handler\HandlerPoolInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handlerMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\Handler\HandlerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Payload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadResourceMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\ResourceModel\Payload::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventType = 'eventType';
        $this->payloadMock->expects(static::any())
            ->method('getEventType')
            ->willReturn($this->eventType);
        $this->payloadResourceMock->expects(static::once())
            ->method('save')
            ->with($this->payloadMock);

        $this->processor = new Processor(
            $this->payloadDataObjectFactoryMock,
            $this->payloadResourceMock,
            $this->handlerPoolMock
        );
    }


    public function testHandleException()
    {
        $message = 'Exception message';
        $exception = new \Exception($message);
        $this->handlerPoolMock->expects(static::once())
            ->method('get')
            ->with($this->eventType)
            ->willThrowException($exception);
        $this->payloadMock->expects(static::once())
            ->method('setStatus')
            ->with(PayloadInterface::STATUS_FAILED);
        $this->payloadMock->expects(static::once())
            ->method('setDetails')
            ->with($message)
            ->willReturnSelf();
        $this->processor->process($this->payloadMock);
    }

    public function testProcess()
    {
        $message = 'Handler message';
        $this->handlerPoolMock->expects(static::once())
            ->method('get')
            ->with($this->eventType)
            ->willReturn($this->handlerMock);
        $this->payloadDataObjectFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->payloadMock)
            ->willReturn($this->payloadDataObjectMock);
        $this->handlerMock->expects(static::once())
            ->method('handle')
            ->with(['payload' => $this->payloadDataObjectMock])
            ->willReturn($message);
        $this->payloadMock->expects(static::once())
            ->method('setStatus')
            ->with(PayloadInterface::STATUS_PROCESSED);
        $this->payloadMock->expects(static::once())
            ->method('setDetails')
            ->with($message)
            ->willReturnSelf();
        $this->processor->process($this->payloadMock);
    }
}
