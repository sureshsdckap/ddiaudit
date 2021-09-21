<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Test\Unit\Model\Status;

use AuthorizeNet\Webhooks\Model\Status\DataProvider;
use PHPUnit\Framework\TestCase;

class DataProviderTest extends TestCase
{
    /**
     * @var \Magento\Framework\Api\Search\ReportingInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportingMock;

    /**
     * @var \Magento\Framework\Api\Search\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var \AuthorizeNet\Webhooks\Model\Webhooks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $webhooksMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \AuthorizeNet\Webhooks\Model\Status\DataProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataProvider;

    protected function setUp()
    {
        $this->reportingMock = $this->getMockBuilder(\Magento\Framework\Api\Search\ReportingInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->webhooksMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Webhooks::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataProvider = new DataProvider(
            '',
            '',
            '',
            $this->reportingMock,
            $this->searchCriteriaBuilderMock,
            $this->requestMock,
            $this->filterBuilderMock,
            $this->webhooksMock,
            $this->messageManagerMock,
            [],
            []
        );
    }

    /**
     * @dataProvider testGetDataDataProvider
     */
    public function testGetData($item, $result)
    {


        $this->webhooksMock->expects(static::any())
            ->method('getWebhooksList')
            ->willReturn($item);

        if ($item == false) {
            $this->messageManagerMock->expects(static::once())
                ->method('addNoticeMessage')
                ->with(__('Unable to load registered webhooks list. Please check your Login ID and Transaction Key.'));
        }

        static::assertEquals($result, $this->dataProvider->getData());
    }

    /**
     * @return array
     */
    public function testGetDataDataProvider()
    {
        $type = 'net.authorize.payment.priorAuthCapture.created';
        $types = $type . PHP_EOL;
        $item = new \stdClass();
        $item->eventTypes = [$type];
        $item->webhookId = '10001';
        $item->name = 'm2_priorAuthCapture_created';
        $item->url = 'm2_priorAuthCapture_created';
        $item->status = 'active';
        return [
            [
                false,
                [
                    'items' => []
                ]
            ],
            [
                [$item],
                [
                    'items' =>
                        [
                            [
                                'id_field_name' => 'webhook_id',
                                'webhook_id' => $item->webhookId,
                                'name' => $item->name,
                                'url' => $item->url,
                                'types' => $types,
                                'status' => $item->status
                            ]
                        ]
                ]
            ]
        ];
    }
}
