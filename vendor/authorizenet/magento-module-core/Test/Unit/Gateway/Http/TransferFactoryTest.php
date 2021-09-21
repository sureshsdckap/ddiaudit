<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http;

use PHPUnit\Framework\TestCase;

class TransferFactoryTest extends TestCase
{

    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferBuilderMock;

    /**
     * @var TransferFactory
     */
    protected $transferFactory;

    protected function setUp()
    {
        $this->transferBuilderMock = $this
            ->getMockBuilder(\Magento\Payment\Gateway\Http\TransferBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->transferFactory = new TransferFactory(
            $this->transferBuilderMock
        );
    }

    public function testCreate()
    {
        
        $request = ['someValue'];
        
        $this->transferBuilderMock->expects(static::once())
            ->method('setBody')
            ->with($request)
            ->willReturnSelf();
        
        $this->transferBuilderMock->expects(static::once())
            ->method('build')
            ->willReturnSelf();
        
        $this->assertEquals($this->transferBuilderMock, $this->transferFactory->create($request));
    }
}
