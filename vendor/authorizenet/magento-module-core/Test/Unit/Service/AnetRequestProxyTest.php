<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Service;

use PHPUnit\Framework\TestCase;

class AnetRequestProxyTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Model\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestSerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerMock;

    /**
     * @var \net\authorize\util\HttpClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClientMock;

    /**
     * @var AnetRequestProxy
     */
    protected $requestProxy;
    
    /**
     * @var \net\authorize\api\contract\v1\ANetApiRequestType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \net\authorize\api\contract\v1\ANetApiResponseType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        $this->filesystemMock = $this
            ->getMockBuilder(\Magento\Framework\Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDirectoryRead', 'getAbsolutePath'])
            ->getMock();
        
        $this->loggerMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger::class)->disableOriginalConstructor()->getMock();
        $this->serializerMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestSerializerInterface::class)->disableOriginalConstructor()->getMock();
        $this->httpClientMock = $this->getMockBuilder(\net\authorize\util\HttpClient::class)->disableOriginalConstructor()->getMock();
        $this->requestMock = $this->getMockBuilder(\net\authorize\api\contract\v1\ANetApiRequestType::class)->disableOriginalConstructor()->getMock();
        $this->responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\ANetApiResponseType::class)->disableOriginalConstructor()->getMock();

        $this->requestProxy = new AnetRequestProxy(
            $this->loggerMock,
            $this->httpClientMock,
            $this->serializerMock
        );
    }

    public function testExecuteWithApiResponse()
    {
        
        $xmlRequest = '<?xml version="1.0"?><someRequest><someRequestParams><param1>value1</param1></someRequestParams></someRequest>';
        $xmlResponse = '<?xml version="1.0"?><someResponse><someRequestParams><param1>value1</param1></someRequestParams></someResponse>';

        $requestType = 'createTransactionRequest';

        $this->requestProxy
            ->setRequest($this->requestMock)
            ->setRequestType($requestType);
        
        $this->serializerMock->expects(static::once())
            ->method('serialize')
            ->with($this->requestMock)
            ->willReturn($xmlRequest);
        
        $this->serializerMock->expects(static::once())
            ->method('deserialize')
            ->with($xmlResponse, $requestType)
            ->willReturn($this->responseMock);
        
        $this->httpClientMock->expects(static::once())
            ->method('_sendRequest')
            ->with($xmlRequest)
            ->willReturn($xmlResponse);

        static::assertEquals(
            $this->responseMock,
            $this->requestProxy->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::CUSTOM)
        );
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Error getting valid response from api. Check log file for error details
     */
    public function testExecuteWithApiResponseWithException()
    {

        $xmlRequest = '<?xml version="1.0"?><someRequest><someRequestParams><param1>value1</param1></someRequestParams></someRequest>';
        $xmlResponse = '<?xml version="1.0"?><someResponse><someRequestParams><param1>value1</param1></someRequestParams></someResponse>';

        $requestType = 'createTransactionRequest';
        
        $this->requestMock->expects(static::once())
            ->method('setClientId')
            ->with("sdk-php-" . \net\authorize\api\constants\ANetEnvironment::VERSION);

        $this->requestProxy
            ->setRequest($this->requestMock)
            ->setRequestType($requestType);

        $this->serializerMock->expects(static::once())
            ->method('serialize')
            ->with($this->requestMock)
            ->willReturn($xmlRequest);

        $this->serializerMock->expects(static::never())
            ->method('deserialize')
            ->with($xmlResponse, $requestType, 'xml')
            ->willReturn($this->responseMock);

        $this->httpClientMock->expects(static::once())
            ->method('_sendRequest')
            ->with($xmlRequest)
            ->willReturn(false);

        static::assertEquals(
            $this->responseMock,
            $this->requestProxy->executeWithApiResponse(\net\authorize\api\constants\ANetEnvironment::CUSTOM)
        );
    }
}
