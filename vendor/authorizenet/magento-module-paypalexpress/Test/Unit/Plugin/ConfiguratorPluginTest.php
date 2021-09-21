<?php
/**
 *
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Plugin;

use AuthorizeNet\PayPalExpress\Plugin\ConfiguratorPlugin;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ConfiguratorPluginTest extends TestCase
{


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

        $this->subjectMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Merchant\Configurator::class)->disableOriginalConstructor()->getMock();
        $config = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new ConfiguratorPlugin($config);
        parent::setUp();
    }

    /**
     * @param $details
     * @param $expectedEnabled
     * @dataProvider dataProviderTestAroundGetSectionsData
     */
    public function testAroundGetSectionsData($details, $expectedEnabled)
    {
        $proceedMock = $this->getMockBuilder(\stdClass::class)->setMethods(['getSectionsData'])->getMock();
        //make sure that parent method is called
        $proceedMock->expects(static::once())->method('getSectionsData')->with($details)->willReturn([]);

        $results = $this->plugin->aroundGetSectionsData(
            $this->subjectMock,
            [$proceedMock, 'getSectionsData'],
            $details
        );

        static::assertEquals($expectedEnabled, $results['data.paypal_express_enabled']);
    }

    public function dataProviderTestAroundGetSectionsData()
    {
        return [
            [
                'details' => [
                    'paymentMethods' => [
                        'Discover',
                        'MasterCard',
                        'Paypal',
                        'VisaCheckout',
                    ]
                ],
                'expectedEnabled' => true,
            ],
            [
                'details' => [
                    'paymentMethods' => [
                        'Discover',
                        'MasterCard',
                        'VisaCheckout',
                    ]
                ],
                'expectedEnabled' => false,
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
            ['paypal_express_enabled', 'payment/anet_paypal_express/active'],
        ];
    }
}
