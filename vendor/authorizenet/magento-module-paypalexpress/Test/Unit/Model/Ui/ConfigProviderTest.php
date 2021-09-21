<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Model\Ui;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Model\Ui\ConfigProvider;
use AuthorizeNet\PayPalExpress\Gateway\Config\Config;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    const PAYPAL_INIT_URL = 'anet_paypal_express/checkout/initialize/';

    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilder;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configProvider;

    protected function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlBuilder = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider(
            $this->config,
            $this->urlBuilder
        );
    }

    /**
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
        $this->urlBuilder->expects(static::once())
            ->method('getDirectUrl')
            ->with(self::PAYPAL_INIT_URL)
            ->willReturn(self::PAYPAL_INIT_URL);

        foreach ($config as $method => $value) {
            $this->config->expects(static::once())
                ->method($method)
                ->willReturn($value);
        }

        static::assertEquals($expected, $this->configProvider->getConfig());
    }

    /**
     * @return array
     */
    public function getConfigDataProvider()
    {
        return [
            [
                'config' => [
                    'isActive' => true,
                    'getTitle' => 'PayPal Express',
                    'isTestMode' => true
                ],
                'expected' => [
                    'payment' => [
                        Config::CODE => [
                            'active' => true,
                            'title' => 'PayPal Express',
                            'test' => true,
                            'initActionUrl' => self::PAYPAL_INIT_URL
                        ]
                    ]
                ]
            ]
        ];
    }
}
