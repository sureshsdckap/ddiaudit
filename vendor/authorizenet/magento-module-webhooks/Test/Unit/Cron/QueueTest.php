<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Cron;

use AuthorizeNet\Webhooks\Cron\Queue;
use PHPUnit\Framework\TestCase;
use AuthorizeNet\Webhooks\Api\PayloadInterface;

class QueueTest extends TestCase
{
    /**
     * @var Queue
     */
    protected $queue;
    /**
     * @var \AuthorizeNet\Webhooks\Model\ResourceModel\Payload\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\ResourceModel\Payload\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\ProcessorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadProcessorMock;

    protected function setUp()
    {
        $this->collectionFactoryMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\ResourceModel\Payload\CollectionFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\ResourceModel\Payload\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payloadProcessorMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\ProcessorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->collectionFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->queue = new Queue(
            $this->collectionFactoryMock,
            $this->payloadProcessorMock
        );
    }

    public function testExecute()
    {
        $items = [
            $itemMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Payload::class)
                ->disableOriginalConstructor()
                ->getMock()
        ];

        $this->collectionMock->expects(static::once())
            ->method('addFieldToFilter')
            ->with('status', PayloadInterface::STATUS_PENDING)
            ->willReturnSelf();

        $this->collectionMock->expects($this->any())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($items));

        $this->payloadProcessorMock->expects(static::once())
            ->method('process')
            ->with(static::isInstanceOf(get_class($itemMock)))
            ->willReturnSelf();

        $this->queue->execute();
    }
}
