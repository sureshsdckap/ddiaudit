<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\Webhooks;
use PHPUnit\Framework\TestCase;

class WebhooksTest extends TestCase
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;
    /**
     * @var \Magento\Store\Model\Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Client|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    protected $response;

    /**
     * @var Webhooks
     */
    protected $webhooks;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->clientMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $item = new \stdClass();
        $item->name = 'm2_name';
        $item->webhookId = 10001;
        $item2 = new \stdClass();
        $item2->name = 'name';
        $item2->webhookId = 10002;

        $this->response = [
            'status' => 200,
            'data' => [
                $item,
                $item2
            ]
        ];

        $this->clientMock->expects(static::any())
            ->method('get')
            ->willReturnCallback([$this, 'getResponse']);

        $this->webhooks = new Webhooks(
            $this->storeManagerMock,
            $this->clientMock
        );
    }

    /**
     * @dataProvider getWebhooksListDataProvider
     */
    public function testGetWebhooksList($status, $data)
    {
        $this->response['status'] = $status;
        static::assertEquals($data ? $this->response['data'] : null, $this->webhooks->getWebhooksList());
    }

    public function testdeleteWebhooks()
    {
        $id = $this->response['data'][0]->webhookId;
        $this->clientMock->expects(static::any())
            ->method('delete')
            ->with($id)
            ->willReturn(true);
        static::assertEquals([$id => true], $this->webhooks->deleteWebhooks());
    }

    public function testCreateWebhooks()
    {
        $this->clientMock->expects(static::any())
            ->method('post')
            ->willReturn(true);

        $this->storeManagerMock->expects(static::any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects(static::any())
            ->method('getBaseUrl')
            ->willReturn('http://localhost/');

        $result = [
            'priorAuthCapture_created' => 200,
            'refund_created' => 200,
            'void_created' => 200,
            'fraud_approved' => 200,
            'fraud_declined' => 200
        ];
        static::assertEquals($result, $this->webhooks->createWebhooks());
    }

    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getWebhooksListDataProvider()
    {
        return [
            [200, true],
            [400, false]
        ];
    }
}
