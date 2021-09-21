<?php
/**
 *
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\VisaCheckout\Plugin\ConfiguratorPlugin;

class ConfiguratorPluginTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator
     */
    protected $subjectMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|MockObject
     */
    protected $configMock;

    /**
     * @var ConfiguratorPlugin
     */
    protected $plugin;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->subjectMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Merchant\Configurator::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new ConfiguratorPlugin(
            $this->configMock
        );
    }

    /**
     * @param $details
     * @param $apiKey
     * @param $expectedApiKey
     * @param $expectedEnabled
     * @dataProvider dataProviderTestAroundGetSectionsData
     */
    public function testAroundGetSectionsData($details, $apiKey, $expectedApiKey, $expectedEnabled)
    {

        $proceedMock = $this->getMockBuilder(\stdClass::class)->setMethods(['getSectionsData'])->getMock();
        //make sure that parent method is called
        $proceedMock->expects(static::once())->method('getSectionsData')->with($details)->willReturn([]);
        $this->configMock->expects(static::any())->method('getApiKey')->willReturn($apiKey);

        $results = $this->plugin->aroundGetSectionsData($this->subjectMock, [$proceedMock, 'getSectionsData'], $details);

        static::assertNotEmpty($results['data.visa_checkout_text']);
        static::assertInternalType('string', $results['data.visa_checkout_text']);
        static::assertEquals($expectedApiKey, $results['data.visa_checkout_api_key']);
        static::assertEquals($expectedEnabled, $results['data.visa_checkout_enabled']);
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
                        'VisaCheckout',
                    ]
                ],
                'apiKey' => 'SomeKey',
                'expectedApiKey' => 'SomeKey',
                'expectedEnabled' => true,
            ],
            [
                'details' => [
                    'paymentMethods' => [
                        'Discover',
                        'MasterCard',
                        'Visa',
                        'VisaCheckout',
                    ]
                ],
                'apiKey' => null,
                'expectedApiKey' => '',
                'expectedEnabled' => true,
            ],
            [
                'details' => [
                    'paymentMethods' => [
                        'Discover',
                        'MasterCard',
                        'Visa',
                    ]
                ],
                'apiKey' => 'SomeKey',
                'expectedApiKey' => '',
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
            ['visa_checkout_enabled', 'payment/anet_visacheckout/active'],
            ['visa_checkout_api_key', 'payment/anet_visacheckout/api_key'],
        ];
    }

    public function testAfterGetEncryptedFields()
    {
        $result = $this->plugin->afterGetEncryptedFields($this->subjectMock, []);

        static::assertContains('visa_checkout_api_key', $result);
    }
}
