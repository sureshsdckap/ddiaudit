<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Zend\Http\HeadersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $headersFactoryMock;
    /**
     * @var \Zend\Http\Headers|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $headersMock;
    /**
     * @var \Zend\Http\RequestFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestFactoryMock;
    /**
     * @var \Zend\Http\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;
    /**
     * @var \Zend\Stdlib\ParametersFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parametersFactoryMock;
    /**
     * @var \Zend\Stdlib\Parameters|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $parametersMock;
    /**
     * @var \Zend\Http\ClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientFactoryMock;
    /**
     * @var \Zend\Http\Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;
    /**
     * @var \Zend\Http\Response|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;
    /**
     * @var Client
     */
    protected $client;

    protected $result;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->headersFactoryMock = $this->getMockBuilder(\Zend\Http\HeadersFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->headersMock = $this->getMockBuilder(\Zend\Http\Headers::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestFactoryMock = $this->getMockBuilder(\Zend\Http\RequestFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Zend\Http\Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parametersFactoryMock = $this->getMockBuilder(\Zend\Stdlib\ParametersFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parametersMock = $this->getMockBuilder(\Zend\Stdlib\Parameters::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientFactoryMock = $this->getMockBuilder(\Zend\Http\ClientFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(\Zend\Http\Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder(\Zend\Http\Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $loginId = 'login';
        $transactionKey = 'transKey';
        $status = 200;
        $data = '{key:value}';
        $this->result = [
            'status' => $status,
            'data' => json_decode($data)
        ];
        $options = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];

        $this->configMock->expects(static::any())
            ->method('getLoginId')
            ->willReturn($loginId);

        $this->configMock->expects(static::any())
            ->method('getTransKey')
            ->willReturn($transactionKey);

        $this->configMock->expects(static::any())
            ->method('isTestMode')
            ->willReturn(true);

        $this->headersFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->headersMock);

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($loginId . ':' . $transactionKey),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];

        $this->headersMock->expects(static::any())
            ->method('addHeaders')
            ->with($headers);

        $this->requestFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->requestMock);

        $this->requestMock->expects(static::any())
            ->method('setHeaders')
            ->with($this->headersMock);

        $this->requestMock->expects(static::any())
            ->method('setUri')
            ->with(Client::SANDBOX . Client::API_PATH_WEBHOOK . '/endpoint');

        $this->parametersFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->parametersMock);

        $this->requestMock->expects(static::any())
            ->method('setQuery')
            ->with($this->parametersMock);

        $this->clientFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->clientMock);

        $this->clientMock->expects(static::any())
            ->method('setOptions')
            ->with($options);

        $this->clientMock->expects(static::any())
            ->method('send')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->responseMock->expects(static::any())
            ->method('getStatusCode')
            ->willReturn($status);

        $this->responseMock->expects(static::any())
            ->method('getContent')
            ->willReturn($data);

        $this->client = new Client(
            $this->configMock,
            $this->headersFactoryMock,
            $this->requestFactoryMock,
            $this->parametersFactoryMock,
            $this->clientFactoryMock
        );
    }

    public function testGet()
    {
        $this->requestMock->expects(static::any())
            ->method('setMethod')
            ->with('get');
        static::assertEquals($this->result, $this->client->get('endpoint'));
    }

    public function testPost()
    {
        $content = 'content';
        $this->requestMock->expects(static::any())
            ->method('setMethod')
            ->with('post');
        $this->requestMock->expects(static::any())
            ->method('setContent')
            ->with($content);
        static::assertEquals($this->result, $this->client->post($content, 'endpoint'));
    }

    public function testDelete()
    {
        $this->requestMock->expects(static::any())
            ->method('setMethod')
            ->with('delete');
        static::assertEquals($this->result, $this->client->delete('endpoint'));
    }

    public function testPut()
    {
        $content = 'content';
        $this->requestMock->expects(static::any())
            ->method('setMethod')
            ->with('put');
        $this->requestMock->expects(static::any())
            ->method('setContent')
            ->with($content);
        static::assertEquals($this->result, $this->client->put($content, 'endpoint'));
    }
}
