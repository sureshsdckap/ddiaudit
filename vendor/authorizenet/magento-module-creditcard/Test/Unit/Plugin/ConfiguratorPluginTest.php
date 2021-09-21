<?php
/**
 *
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\CreditCard\Plugin\ConfiguratorPlugin;

class ConfiguratorPluginTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Model\CcTypes|MockObject
     */
    protected $cctypesMock;

    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator
     */
    protected $subjectMock;

    /**
     * @var ConfiguratorPlugin
     */
    protected $plugin;

    protected function setUp()
    {
        $this->cctypesMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\CcTypes::class)->disableOriginalConstructor()->getMock();
        $this->subjectMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Merchant\Configurator::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new ConfiguratorPlugin(
            $this->cctypesMock
        );
    }


    /**
     * @param $details
     * @dataProvider dataProviderTestAroundGetSectionsData
     */
    public function testAroundGetSectionsData($details, $expectedCcTypes, $ccEnabled)
    {

        $proceedMock = $this->getMockBuilder(\stdClass::class)->setMethods(['getSectionsData'])->getMock();
        //make sure that parent method is called
        $proceedMock->expects(static::once())->method('getSectionsData')->with($details)->willReturn([]);

        $this->cctypesMock->expects(static::any())->method('getAvailableAuthorizeNetTypes')->willReturn(array_keys(\AuthorizeNet\Core\Model\CcTypes::CC_TYPE_MAP));
        $this->cctypesMock->expects(static::any())->method('getMagentoType')->willReturnArgument(0);

        $results = $this->plugin->aroundGetSectionsData(
            $this->subjectMock,
            [$proceedMock, 'getSectionsData'],
            $details
        );

        static::assertNotEmpty($results['data.cc_types_text']);
        static::assertInternalType('string', $results['data.cc_types_text']);
        static::assertEquals($expectedCcTypes, $results['data.cc_types']);
        static::assertEquals($ccEnabled, $results['data.cc_enabled']);
    }

    public function dataProviderTestAroundGetSectionsData()
    {
        return [
            [
                'details' => [
                    'paymentMethods' => [
                        'Discover',
                        'MasterCard',
                        'Visa',
                        'UnsupportedCard',
                    ]
                ],
                'expectedCcTypes' => [
                    'Discover',
                    'MasterCard',
                    'Visa',
                ],
                'ccEnabled' => true
            ],
            [
                'details' => [
                    'paymentMethods' => [
                    ]
                ],
                'expectedCcTypes' => [
                ],
                'ccEnabled' => false
            ],
        ];
    }

    /**
     * @param $key
     * @param $expectedValue
     * @dataProvider dataProviderTestAfterGetConfigPathMap
     */
    public function testAfterGetConfigPathMap($key, $expectedValue)
    {

        $result = $this->plugin->afterGetConfigPathMap($this->subjectMock, []);
        static::assertEquals($expectedValue, $result[$key]);
    }

    public function dataProviderTestAfterGetConfigPathMap()
    {
        return [
            ['cc_enabled', 'payment/anet_creditcard/active'],
            ['cc_types', 'payment/anet_creditcard/cctypes'],
        ];
    }
}
