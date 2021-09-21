<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Test\Unit\Controller\Cca;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Centinel\Controller\Cca\HandleResponse;
use PHPUnit\Framework\TestCase;

class HandleResponseTest extends TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|MockObject
     */
    protected $contextMock;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|MockObject
     */
    protected $jsonFactoryMock;
    /**
     * @var \Magento\Framework\Controller\Result\Json|MockObject
     */
    protected $jsonMock;
    /**
     * @var \AuthorizeNet\Centinel\Model\Cca\Validator|MockObject
     */
    protected $validatorMock;
    /**
     * @var \Lcobucci\JWT\Parser|MockObject
     */
    protected $parserMock;
    /**
     * @var \Magento\Checkout\Model\Session|MockObject
     */
    protected $sessionMock;
    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;
    /**
     * @var HandleResponse|MockObject
     */
    protected $controller;
    /**
     * @var \AuthorizeNet\Core\Model\Logger|MockObject
     */
    protected $loggerMock;

    /**
     * @var \Lcobucci\JWT\Token|MockObject
     */
    protected $tokenMock;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRequest'])
            ->getMock();

        $this->jsonFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->jsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->validatorMock = $this->createMock(\AuthorizeNet\Centinel\Model\Cca\Validator::class);
        $this->parserMock = $this->createMock(\Lcobucci\JWT\Parser::class);
        $this->sessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['setData'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getPost'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->loggerMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tokenMock = $this->createMock(\Lcobucci\JWT\Token::class);

        $this->jsonFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->jsonMock);

        $this->contextMock->expects(static::once())
            ->method('getRequest')
            ->willReturn($this->requestMock);

        $this->controller = new HandleResponse(
            $this->contextMock,
            $this->jsonFactoryMock,
            $this->validatorMock,
            $this->parserMock,
            $this->sessionMock,
            $this->loggerMock
        );
    }

    /**
     * @param $jwt
     * @param $payload
     * @dataProvider dataProviderTestException
     */
    public function testException($jwt, $payload, $errorMsg)
    {
        $this->requestMock->expects(static::once())
            ->method('getPost')
            ->with('jwt')
            ->willReturn($jwt);

        if ($jwt) {
            $message = 'message';

            $this->parserMock->expects(static::once())
                ->method('parse')
                ->with($jwt)
                ->willReturn($this->tokenMock);

            $this->tokenMock->expects(static::once())
                ->method('getClaim')
                ->with('Payload')
                ->willReturn($payload);

            $this->validatorMock->expects(static::once())
                ->method('validate')
                ->with($this->tokenMock)
                ->willThrowException(new \Exception($message));

            $this->loggerMock->expects(static::once())
                ->method('debug')
                ->with([$payload]);
        }

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(['status' => false, 'error' => $errorMsg]);

        $this->jsonMock->expects(static::once())
            ->method('setHttpResponseCode')
            ->with(400);

        static::assertEquals($this->jsonMock, $this->controller->execute());
    }

    public function testExecute()
    {
        $jwt = 'jwt';

        $this->requestMock->expects(static::once())
            ->method('getPost')
            ->with('jwt')
            ->willReturn($jwt);


        $this->parserMock->expects(static::once())
            ->method('parse')
            ->with($jwt)
            ->willReturn($this->tokenMock);

        $this->validatorMock->expects(static::once())
            ->method('validate')
            ->with($this->tokenMock);

        $payload = new \stdClass();
        $payload->ActionCode = 'actionCode';
        $payload->Payment = new \stdClass();
        $payload->Payment->ExtendedData = new \stdClass();

        $ccaData = $payload->Payment->ExtendedData;
        $ccaData->ccaActionCode = $payload->ActionCode;

        $this->tokenMock->expects(static::once())
            ->method('getClaim')
            ->with('Payload')
            ->willReturn($payload);

        $this->sessionMock->expects(static::once())
            ->method('setData')
            ->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX, $ccaData)
            ->willReturn($payload);

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(['status' => true]);

        static::assertEquals($this->jsonMock, $this->controller->execute());
    }

    public function dataProviderTestException()
    {
        return [
            [
                'jwt' => 'jwt',
                'payload' => 'payload',
                'errorMsg' => 'Something went wrong. CCA failed.'
            ],
            [
                'jwt' => null,
                'payload' => null,
                'errorMsg' => 'JWT should be provided'
            ],
        ];
    }
}
