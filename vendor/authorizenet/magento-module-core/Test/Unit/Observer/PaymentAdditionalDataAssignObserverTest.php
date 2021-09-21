<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Observer;

class PaymentAdditionalDataAssignObserverTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var PaymentAdditionalDataAssignObserver
     */
    protected $observer;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;

    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodMock;
    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataMock;

    protected function setUp()
    {

        $this->observer = new PaymentAdditionalDataAssignObserver();

        $this->eventObserverMock = $this
            ->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getEvent', 'getDataByKey'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->dataMock = $this->getMockBuilder(\Magento\Framework\DataObject::class)->disableOriginalConstructor()->getMock();
        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $this->paymentDataMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
    }


    /**
     * @param $additionalData
     * @param $dataKeys
     * @dataProvider dataProviderTestExecute
     */
    public function testExecute($additionalData, $dataKeys)
    {

        $this->eventObserverMock->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturnSelf();

        $this->eventObserverMock->expects(static::atLeastOnce())
            ->method('getDataByKey')
            ->willReturnMap([
                ['method', $this->methodMock],
                ['data', $this->dataMock],
                ['payment_model', $this->paymentDataMock]
            ]);
        
        $this->dataMock->expects(static::once())
            ->method('getData')
            ->with(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA)
            ->willReturn($additionalData);

        if (is_array($additionalData)) {
            $this->methodMock->expects(static::once())
                ->method('getConfigData')
                ->willReturn($dataKeys);
        }

        $expectedCallArguments = [];
        foreach (explode(',', $dataKeys) as $key) {
            if (isset($additionalData[$key])) {
                $expectedCallArguments[] = [$key, $additionalData[$key]];
            }
        }

        $this->paymentDataMock->expects(static::exactly(count($expectedCallArguments)))
            ->method('setAdditionalInformation')
            ->withConsecutive(...$expectedCallArguments)
            ->willReturnSelf();
            

        $this->observer->execute($this->eventObserverMock);
    }

    public function dataProviderTestExecute()
    {
        return [
            [
                'additionalData' => [
                    'opaque_data' => 'q3nbWWEq34bo1pl35bmo4',
                ],
                'dataKeys' => 'opaque_data',
            ],
            [
                'additionalData' => [
                    'opaque_data' => 'q3nbWWEq34bo1pl35bmo4',
                    'encKey' => 'asdasdasd'
                ],
                'dataKeys' => 'opaque_data,encKey',
            ],
            [
                'additionalData' => [],
                'dataKeys' => 'opaque_data',
            ],
            [
                'additionalData' => [
                    'opaque_data' => 'q3nbWWEq34bo1pl35bmo4',
                ],
                'dataKeys' => '',
            ],
            [
                'additionalData' => false,
                'dataKeys' => 'somekey1,somekey2',
            ],
        ];
    }
}
