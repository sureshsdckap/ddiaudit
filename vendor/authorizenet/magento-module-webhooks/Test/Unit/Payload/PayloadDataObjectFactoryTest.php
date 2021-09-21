<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload;

use AuthorizeNet\Webhooks\Payload\PayloadDataObjectFactory;
use PHPUnit\Framework\TestCase;

class PayloadDataObjectFactoryTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\OrderRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderRepositoryMock;
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionFinderMock;
    /**
     * @var \AuthorizeNet\Webhooks\Api\PayloadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payload;
    /**
     * @var PayloadDataObjectFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->orderRepositoryMock = $this->getMockBuilder(\Magento\Sales\Model\OrderRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionFinderMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\TransactionFinder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new PayloadDataObjectFactory(
            $this->objectManagerMock,
            $this->orderRepositoryMock,
            $this->transactionFinderMock
        );
    }

    /**
     * @dataProvider boolDataProvider
     */
    public function testCreate($value)
    {
        $transactionId = 101;
        $orderId = 111;
        $transaction = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $order = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payload = $this->getMockBuilder(\AuthorizeNet\Webhooks\Api\PayloadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $payloadDO = $this->getMockBuilder(\AuthorizeNet\Webhooks\Payload\PayloadDataObject::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payload->expects(static::any())
            ->method('getPayload')
            ->willReturn(['id' => $transactionId]);
        $this->transactionFinderMock->expects(static::any())
            ->method('getTransaction')
            ->with($transactionId)
            ->willReturn($transaction);
        if ($value) {
            $transaction->expects(static::once())
                ->method('getOrderId')
                ->willReturn($orderId);
            $this->orderRepositoryMock->expects(static::once())
                ->method('get')
                ->with($orderId)
                ->willReturn($order);
            $transaction->expects(static::any())
                ->method('getId')
                ->willReturn($transactionId);
        } else {
            $transaction = null;
            $order = null;
        }

        $this->objectManagerMock->expects(static::once())
            ->method('create')
            ->with(
                \AuthorizeNet\Webhooks\Payload\PayloadDataObject::class,
                [
                    'payload' => $payload,
                    'order' => $order,
                    'transaction' => $transaction,
                ]
            )
            ->willReturn($payloadDO);

        $this->assertEquals($payloadDO, $this->factory->create($payload));
    }

    /**
     * @return array
     */
    public function boolDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
