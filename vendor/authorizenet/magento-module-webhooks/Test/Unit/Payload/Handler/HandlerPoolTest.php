<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Handler;

use AuthorizeNet\Webhooks\Payload\Handler\HandlerInterface;
use AuthorizeNet\Webhooks\Payload\Handler\HandlerPool;
use PHPUnit\Framework\TestCase;

class HandlerPoolTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManager\TMapFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tMapFactory;
    /**
     * @var \Magento\Framework\ObjectManager\TMap|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tMap;
    protected $handlerType;

    protected function setUp()
    {
        $this->tMapFactory = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMapFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->tMap = $this->getMockBuilder(\Magento\Framework\ObjectManager\TMap::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->handlerType = 'handler';
    }

    public function testGet()
    {

        $type = HandlerInterface::class;

        $handler = $this->getMockBuilder($type)
            ->getMockForAbstractClass();

        $this->tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [$type],
                    'type' => HandlerInterface::class
                ]
            )
            ->willReturn($this->tMap);

        $this->tMap->expects(static::once())
            ->method('offsetExists')
            ->with($this->handlerType)
            ->willReturn(true);
        $this->tMap->expects(static::once())
            ->method('offsetGet')
            ->with($this->handlerType)
            ->willReturn($handler);

        $pool = new HandlerPool($this->tMapFactory, [HandlerInterface::class]);

        static::assertSame($handler, $pool->get($this->handlerType));
    }

    public function testGetException()
    {
        $this->expectException(\Magento\Framework\Exception\NotFoundException::class);
        $this->expectExceptionMessage('Handler for type ' . $this->handlerType . ' does not exist.');

        $this->tMapFactory->expects(static::once())
            ->method('create')
            ->with(
                [
                    'array' => [],
                    'type' => HandlerInterface::class
                ]
            )
            ->willReturn($this->tMap);
        $this->tMap->expects(static::once())
            ->method('offsetExists')
            ->with($this->handlerType)
            ->willReturn(false);

        $pool = new HandlerPool($this->tMapFactory, []);
        $pool->get($this->handlerType);
    }
}
