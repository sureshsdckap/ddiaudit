<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload;

use AuthorizeNet\Webhooks\Payload\PayloadDataObject;
use PHPUnit\Framework\TestCase;

class PayloadDataObjectTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Webhooks\Api\PayloadInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payload;
    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transaction;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;
    /**
     * @var PayloadDataObject
     */
    protected $payloadDO;

    protected function setUp()
    {
        $this->payload = $this->getMockBuilder(\AuthorizeNet\Webhooks\Api\PayloadInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transaction = $this->getMockBuilder(\Magento\Sales\Api\Data\TransactionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->order = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->payloadDO = new PayloadDataObject(
            $this->payload,
            $this->transaction,
            $this->order
        );
    }

    public function testGetPayload()
    {
        $this->assertEquals($this->payload, $this->payloadDO->getPayload());
    }

    public function testGetOrder()
    {
        $this->assertEquals($this->order, $this->payloadDO->getOrder());
    }

    public function testGetTransaction()
    {
        $this->assertEquals($this->transaction, $this->payloadDO->getTransaction());
    }
}
