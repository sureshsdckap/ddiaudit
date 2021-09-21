<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

use PHPUnit\Framework\TestCase;

class AbstractClientTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestProxyFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proxyFactoryMock;
    /**
     * @var \AuthorizeNet\Core\Model\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestProxy|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proxyMock;

    /**
     * @var AbstractClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionClientMock;

    /**
     * @var \net\authorize\api\contract\v1\ANetApiRequestType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferObjectMock;

    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestSerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerMock;

    protected function setUp()
    {
        $this->proxyFactoryMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestProxyFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->proxyMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestProxy::class)->disableOriginalConstructor()->getMock();

        $this->proxyFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->proxyMock);

        $this->loggerMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(\net\authorize\api\contract\v1\ANetApiRequestType::class)->disableOriginalConstructor()->getMock();
        $this->serializerMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestSerializerInterface::class)->disableOriginalConstructor()->getMock();

        $arguments = [
            $this->loggerMock,
            $this->configMock,
            $this->serializerMock,
            $this->proxyFactoryMock
        ];
        $this->transactionClientMock = $this->getMockForAbstractClass(
            \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::class,
            $arguments,
            '',
            true,
            true,
            true,
            ['process']
        );
    }

    public function testPlaceRequest()
    {
        $expectedResponse = 'someresponse';

        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();

        $this->transferObjectMock->expects(static::once())
            ->method('getBody')
            ->willReturn(['request' => $this->requestMock]);

        $this->transactionClientMock
            ->expects(static::once())
            ->method('process')
            ->with($this->requestMock)
            ->willReturn($expectedResponse);

        static::assertEquals([$expectedResponse], $this->transactionClientMock->placeRequest($this->transferObjectMock));
    }

    public function testPlaceRequestWithException()
    {
        $exceptionMessage = 'something went wrong';
        $exception = new \Exception($exceptionMessage);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage($exceptionMessage);
        
        $expectedResponse = 'someresponse';

        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();

        $this->transferObjectMock->expects(static::once())
            ->method('getBody')
            ->willReturn(['request' => $this->requestMock]);

        $this->transactionClientMock
            ->expects(static::once())
            ->method('process')
            ->with($this->requestMock)
            ->will(static::throwException($exception));
        
        $this->loggerMock->expects(static::once())
            ->method('critical')
            ->with($exceptionMessage);

        static::assertEquals([$expectedResponse], $this->transactionClientMock->placeRequest($this->transferObjectMock));
    }
}
