<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Model\Ui;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\CreditCard\Gateway\Config\Config;
use AuthorizeNet\CreditCard\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var Config|MockObject
     */
    private $config;

    /**
     * @var ConfigProvider|MockObject
     */
    private $configProvider;

    public function setUp()
    {
        $this->config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider(
            $this->config
        );
    }

    /**
     * @param array $config
     * @param array $expected
     * @dataProvider getConfigDataProvider
     */
    public function testGetConfig($config, $expected)
    {
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
                    'getTitle' => 'Credit Card',
                    'getAvailableCardTypes' => ['CUP', 'AE', 'VI', 'MC', 'DI', 'JCB', 'DN', 'MI'],
                    'getLoginId' => '10gin1D',
                    'getClientKey' => 'c1i3n7Key'
                ],
                'expected' => [
                    'payment' => [
                        Config::CODE => [
                            'active' => true,
                            'title' => 'Credit Card',
                            'availableCardTypes' => ['CUP', 'AE', 'VI', 'MC', 'DI', 'JCB', 'DN', 'MI'],
                            'vaultCode' => Config::VAULT_CODE,
                            'loginId' => '10gin1D',
                            'clientKey' => 'c1i3n7Key',
                            'centinelActive' => null
                        ]
                    ]
                ]
            ]
        ];
    }

    public function testGetCode()
    {
        static::assertEquals(Config::CODE, $this->configProvider->getCode());
    }
}
