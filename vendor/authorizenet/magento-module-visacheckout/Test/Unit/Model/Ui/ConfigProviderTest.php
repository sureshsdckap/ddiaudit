<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Model\Ui;

use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    
    /**
     * @var \Magento\Framework\Locale\ResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeResolverMock;
    
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->localeResolverMock = $this->getMockBuilder(\Magento\Framework\Locale\ResolverInterface::class)->getMockForAbstractClass();

        $this->configProvider = new ConfigProvider(
            $this->configMock,
            $this->localeResolverMock
        );
    }

    /**
     * @param $expectedResult
     * @dataProvider dataProviderTestGetConfig
     */
    public function testGetConfig($expectedResult)
    {
        
        $config = $expectedResult['payment']['anet_visacheckout'];
        
        $this->configMock->expects(static::once())
            ->method('isActive')
            ->willReturn($config['isActive']);
        
        $this->configMock->expects(static::once())
            ->method('getTitle')
            ->willReturn($config['title']);
        
        $this->configMock->expects(static::once())
            ->method('getApiKey')
            ->willReturn($config['api_key']);
        
        static::assertEquals($expectedResult, $this->configProvider->getConfig());
    }

    public function dataProviderTestGetConfig()
    {
        return [
            [
                'expectedResult' => [
                    'payment' => [
                        'anet_visacheckout' => [
                            'isActive' => true,
                            'title' => 'Visa Checkout',
                            'api_key' => 'api_key_stub_data'
                        ]
                    ]
                ]
            ],
            [
                'expectedResult' => [
                    'payment' => [
                        'anet_visacheckout' => [
                            'isActive' => false,
                            'title' => 'Visa Checkout',
                            'api_key' => 'api_key_stub_data'
                        ]
                    ]
                ]
            ],
        ];
    }
}
