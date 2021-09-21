<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

use PHPUnit\Framework\TestCase;

class TransactionClientTest extends TestCase
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
     * @var TransactionClient
     */
    protected $transactionClient;

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

        $this->transactionClient = new TransactionClient(
            $this->loggerMock,
            $this->configMock,
            $this->serializerMock,
            $this->proxyFactoryMock
        );
    }

    /**
     * @param $isTestMode
     * @param $endpoint
     * @dataProvider dataProviderTestProcess
     */
    public function testProcess($isTestMode, $endpoint)
    {

        $this->configMock->expects(static::once())
            ->method('isTestMode')
            ->willReturn($isTestMode);

        $expectedResponse = 'someResponse';

        $this->prepareProxyMock(
            $isTestMode,
            $endpoint,
            $expectedResponse,
            \AuthorizeNet\Core\Service\AnetRequestProxy::TYPE_CREATE_TRANSACTION
        );

        static::assertEquals($expectedResponse, $this->transactionClient->process($this->requestMock));
    }
    
    public function dataProviderTestProcess()
    {
        return [
            ['isTestMode' => true, 'endPoint' => \net\authorize\api\constants\ANetEnvironment::SANDBOX],
            ['isTestMode' => false, 'endPoint' => \net\authorize\api\constants\ANetEnvironment::PRODUCTION]
        ];
    }

    /**
     * @param $isTestMode
     * @param $endpoint
     * @param $expectedResponse
     * @throws \Exception
     * @dataProvider dataProviderPlaceRequest
     */
    public function testPlaceRequest($isTestMode, $endpoint, $expectedResponse)
    {
        
        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionRequest::class)->disableOriginalConstructor()->getMock();

        $this->transferObjectMock->expects(static::once())
            ->method('getBody')
            ->willReturn(['request' => $this->requestMock]);

        $this->configMock->expects(static::once())
            ->method('isTestMode')
            ->willReturn($isTestMode);
        
        $this->prepareProxyMock($isTestMode, $endpoint, $expectedResponse, \AuthorizeNet\Core\Service\AnetRequestProxy::TYPE_CREATE_TRANSACTION);

        $transactionRequestMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionRequestType::class)->disableOriginalConstructor()->getMock();

        $this->requestMock->expects(static::never())
            ->method('getTransactionRequest')
            ->willReturn($transactionRequestMock);

        $this->requestMock->expects(static::never())
            ->method('setTransactionRequest')
            ->with($transactionRequestMock)
            ->willReturnSelf();
        
        $transactionRequestMock->expects(static::never())
            ->method('setPayment')
            ->with(new \net\authorize\api\contract\v1\PaymentType)
            ->willReturnSelf();
        
        $this->requestMock->expects(static::never())
            ->method('setMerchantAuthentication')
            ->with(new \net\authorize\api\contract\v1\MerchantAuthenticationType())
            ->willReturnSelf();
        
        $this->loggerMock->expects(static::once())
            ->method('debug')
            ->with([
                'request' => $this->requestMock,
                'response' => $expectedResponse
            ]);

        $this->serializerMock->expects(static::any())->method('toArray')->willReturnArgument(0);

        static::assertEquals([$expectedResponse], $this->transactionClient->placeRequest($this->transferObjectMock));
    }


    public function dataProviderPlaceRequest()
    {
        return [
            [
                'isTestMode' => true,
                'endPoint' => \net\authorize\api\constants\ANetEnvironment::SANDBOX,
                'expectedResponse' => 'someResponse'
            ],
            [
                'isTestMode' => false,
                'endPoint' => \net\authorize\api\constants\ANetEnvironment::PRODUCTION,
                'expectedResponse' => false
            ]
        ];
    }
    
    private function prepareProxyMock($isTestMode, $endpoint, $expectedResponse, $transactionType)
    {
        $this->proxyMock->expects(static::once())
            ->method('setRequest')
            ->with($this->requestMock)
            ->willReturnSelf();

        $this->proxyMock->expects(static::once())
            ->method('executeWithApiResponse')
            ->with($endpoint)
            ->willReturn($expectedResponse);

        $this->proxyMock->expects(static::once())
            ->method('setRequestType')
            ->with($transactionType)
            ->willReturnSelf();
    }
}
