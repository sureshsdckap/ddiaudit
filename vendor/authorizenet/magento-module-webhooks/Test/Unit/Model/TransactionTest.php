<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\Transaction;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;
    /**
     * @var \AuthorizeNet\Core\Model\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\PayloadFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadFactoryMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Payload|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payloadMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transaction;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\Webapi\Rest\Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->loggerMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadFactoryMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\PayloadFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Payload::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transaction = new Transaction(
            $this->requestMock,
            $this->loggerMock,
            $this->payloadFactoryMock,
            $this->configMock
        );
    }

    /**
     * @dataProvider testTransactionDataProvider
     */
    public function testTransaction($value)
    {
        $notificationId = 'a4fc063a-8fa3-40fb-8e12-c0ce45b3641a';
        $eventType = 'net.authorize.payment.priorAuthCapture.created';
        $eventDate = '2018-01-15T09:31:55.8693887Z';
        $webhookId = '46fc94f1-1950-4a02-b2ed-8f769df1d744';
        $payload = '{"responseCode":1,"authCode":"AIACQV","avsResponse":"Y","authAmount":15.84,"entityName":"transaction","id":"40009535084"}}';
        $content = 'content';
        $key = 'key';

        $data = [
            'notification_id' => $notificationId,
            'event_type' => $eventType,
            'event_date' => $eventDate,
            'webhook_id' => $webhookId,
            'payload' => json_encode($payload),
            'status' => \AuthorizeNet\Webhooks\Api\PayloadInterface::STATUS_PENDING
        ];

        $this->payloadFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->payloadMock);

        $this->payloadMock->expects(static::any())
            ->method('setData')
            ->with($data)
            ->willReturn($this->payloadMock);

        $header = 'X-ANET-Signature';

        $this->requestMock->expects(static::any())
            ->method('getHeader')
            ->with($header)
            ->willReturn($value);

        $this->requestMock->expects(static::any())
            ->method('getContent')
            ->willReturn($content);

        $this->configMock->expects(static::any())
            ->method('getSignatureKey')
            ->willReturn($key);

        $this->transaction->transaction($notificationId, $eventType, $eventDate, $webhookId, $payload);
    }

    /**
     * @return array
     */
    public function testTransactionDataProvider()
    {
        return [
            ['sha512-' . strtoupper(hash_hmac('sha512', 'content', 'key'))],
            ['wrongSignature']
        ];
    }
}
