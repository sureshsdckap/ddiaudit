<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */
namespace AuthorizeNet\ECheck\Test\Unit\Model\Ui;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\ECheck\Gateway\Config\Config;
use AuthorizeNet\ECheck\Model\Ui\ConfigProvider;
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

    protected function setUp()
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
                    'getTitle' => 'ECheck',
                    'getAgreementTemplate' => 'agreement template {{test}}',
                    'getAccountTypeOptions' => [
                        ['value' => '', 'label' => __('--Please Select--')],
                        ['value' => 'checking', 'label' => 'Checking'],
                        ['value' => 'savings', 'label' => 'Savings']
                    ],
                    'getLoginId' => '10gin1D',
                    'getClientKey' => 'c1i3n7Key'
                ],
                'expected' => [
                    'payment' => [
                        Config::CODE => [
                            'active' => true,
                            'title' => 'ECheck',
                            'agreementTemplate' => 'agreement template {{test}}',
                            'accountTypeOptions' => [
                                ['value' => '', 'label' => __('--Please Select--')],
                                ['value' => 'checking', 'label' => 'Checking'],
                                ['value' => 'savings', 'label' => 'Savings']
                            ],
                            'loginId' => '10gin1D',
                            'clientKey' => 'c1i3n7Key',
                            'vaultCode' => 'anet_echeck_vault'
                        ]
                    ]
                ]
            ]
        ];
    }
}
