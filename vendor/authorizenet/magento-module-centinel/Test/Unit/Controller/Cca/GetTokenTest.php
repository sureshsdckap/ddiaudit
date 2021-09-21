<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Test\Unit\Controller\Cca;

use AuthorizeNet\Centinel\Controller\Cca\GetToken;
use PHPUnit\Framework\TestCase;

class GetTokenTest extends TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonFactoryMock;
    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonMock;
    /**
     * @var \AuthorizeNet\Centinel\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Lcobucci\JWT\BuilderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderFactoryMock;
    /**
     * @var \Lcobucci\JWT\Builder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;
    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $randomMock;
    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sha256Mock;
    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $sessionMock;

    /**
     * @var GetToken|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $controllerMock;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Backend\App\Action\Context::class);
        $this->jsonFactoryMock = $this->createMock(\Magento\Framework\Controller\Result\JsonFactory::class);
        $this->jsonMock = $this->createMock(\Magento\Framework\Controller\Result\Json::class);
        $this->configMock = $this->createMock(\AuthorizeNet\Centinel\Model\Config::class);
        $this->builderFactoryMock = $this->createMock(\Lcobucci\JWT\BuilderFactory::class);
        $this->builderMock = $this->createMock(\Lcobucci\JWT\Builder::class);
        $this->randomMock = $this->createMock(\Magento\Framework\Math\Random::class);
        $this->sha256Mock = $this->createMock(\Lcobucci\JWT\Signer\Hmac\Sha256::class);
        $this->sessionMock = $this->createMock(\Magento\Checkout\Model\Session::class);

        $this->controllerMock = $this->getMockBuilder(GetToken::class)
            ->setConstructorArgs([
                $this->contextMock,
                $this->jsonFactoryMock,
                $this->configMock,
                $this->builderFactoryMock,
                $this->sha256Mock,
                $this->randomMock,
                $this->sessionMock
            ])
            ->setMethods(['getTime'])
            ->getMock();

        $this->jsonFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->jsonMock);
    }

    public function testExecute()
    {
        $tokenMock = $this->createMock(\Lcobucci\JWT\Token::class);
        $quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods(['getBaseGrandTotal', 'getBaseCurrencyCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $time = time();
        $this->controllerMock->expects(static::once())
            ->method('getTime')
            ->willReturn($time);

        $this->sessionMock->expects(static::once())
            ->method('getQuote')
            ->willReturn($quoteMock);

        $baseGrandTotal = 10;
        $baseCurrencyCode = 'usd';
        $quoteMock->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn($baseGrandTotal);
        $quoteMock->expects(static::once())
            ->method('getBaseCurrencyCode')
            ->willReturn($baseCurrencyCode);

        $orderHash = 'order_123';
        $this->randomMock->expects(static::at(0))
            ->method('getUniqueHash')
            ->with('order_')
            ->willReturn($orderHash);

        $jwtHash = 'jwt_123';
        $this->randomMock->expects(static::at(1))
            ->method('getUniqueHash')
            ->with('jwt_')
            ->willReturn($jwtHash);

        $this->builderFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->builderMock);

        $this->builderMock->expects(static::once())
            ->method('setId')
            ->with($jwtHash, true)
            ->willReturnSelf();

        $apiId = 'apiId';
        $this->configMock->expects(static::once())
            ->method('getApiId')
            ->willReturn($apiId);

        $this->builderMock->expects(static::once())
            ->method('setIssuer')
            ->with($apiId)
            ->willReturnSelf();

        $this->builderMock->expects(static::once())
            ->method('setIssuedAt')
            ->with($time)
            ->willReturnSelf();

        $this->builderMock->expects(static::once())
            ->method('setExpiration')
            ->with($time + GetToken::JWT_EXP_TIME)
            ->willReturnSelf();

        $unitId = 'unitId';
        $this->configMock->expects(static::once())
            ->method('getUnitId')
            ->willReturn($unitId);

        $payload = [
            'OrderDetails' => [
                'OrderNumber' => $orderHash,
                'Amount' => round($baseGrandTotal * 100),
                'CurrencyCode' => $baseCurrencyCode
            ]
        ];

        $this->builderMock->expects(static::exactly(3))
            ->method('set')
            ->withConsecutive(
                ['OrgUnitId', $unitId],
                ['Payload', $payload],
                ['ObjectifyPayload', true]
            )
            ->willReturnSelf();

        $apiKey = 'apiKey';
        $this->configMock->expects(static::once())
            ->method('getApiKey')
            ->willReturn($apiKey);

        $this->builderMock->expects(static::once())
            ->method('sign')
            ->with($this->sha256Mock, $apiKey)
            ->willReturnSelf();

        $this->builderMock->expects(static::once())
            ->method('getToken')
            ->willReturn($tokenMock);
        $token = 'token';
        $tokenMock->expects(static::once())
            ->method('__toString')
            ->willReturn($token);

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(['status' => true, 'jwt' => $token])
            ->willReturnSelf();

        static::assertEquals($this->jsonMock, $this->controllerMock->execute());
    }

    public function testException()
    {
        $message = 'message';
        $this->sessionMock->expects(static::once())
            ->method('getQuote')
            ->willThrowException(new \Exception($message));

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(['status' => false, 'error' => $message]);

        $this->jsonMock->expects(static::once())
            ->method('setHttpResponseCode')
            ->with(400);

        static::assertEquals($this->jsonMock, $this->controllerMock->execute());
    }
}
