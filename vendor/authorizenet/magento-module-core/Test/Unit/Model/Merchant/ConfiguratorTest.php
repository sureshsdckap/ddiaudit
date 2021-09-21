<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Model\Merchant;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

use AuthorizeNet\Core\Model\Merchant\Configurator;

class ConfiguratorTest extends TestCase
{


    /**
     * @var \Magento\Payment\Gateway\CommandInterface|MockObject
     */
    protected $commandMock;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface|MockObject
     */
    protected $encryptorMock;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface|MockObject
     */
    protected $configWriterMock;

    /**
     * @var Configurator
     */
    protected $configurator;

    protected function setUp()
    {

        $this->commandMock = $this->getMockBuilder(\Magento\Payment\Gateway\CommandInterface::class)->getMockForAbstractClass();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)->getMockForAbstractClass();
        $this->configWriterMock = $this->getMockBuilder(\Magento\Framework\App\Config\Storage\WriterInterface::class)->getMockForAbstractClass();

        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore', 'getCurrentCurrency', 'getCode'])
            ->getMockForAbstractClass();

        $this->configurator = new Configurator(
            $this->commandMock,
            $this->storeManagerMock,
            $this->encryptorMock,
            $this->configWriterMock
        );
    }

    /**
     * @dataProvider dataProviderTestLoadConfig
     */
    public function testLoadConfig($apiCurrency)
    {

        $credentials = [
            'loginId' => 'wr2r12',
            'transactionKey' => 'o1j3oj132',
        ];

        $info = [
            'currencies' => [$apiCurrency],
            'clientKey' => '1ojfm1ojoj13ojo2kgo2kgv2g',
        ];

        $this->storeManagerMock->method('getStore')->willReturnSelf();
        $this->storeManagerMock->method('getCurrentCurrency')->willReturnSelf();
        $this->storeManagerMock->method('getCode')->willReturn('USD');

        $this->commandMock->expects(static::once())->method('execute')->with($credentials)->willReturn($info);

        $result = $this->configurator->loadConfig($credentials['loginId'], $credentials['transactionKey']);

        static::assertEquals($info['clientKey'], $result['data.client_key']);
        static::assertNotEmpty($result['data.client_key_text']);
        static::assertNotEmpty($result['data.base_currency_text']);
    }

    public function dataProviderTestLoadConfig()
    {
        return [
            ['apiCurrency' => 'EUR'],
            ['apiCurrency' => 'USD'],
        ];
    }


    /**
     * @param $paramName
     * @param $paramValue
     * @param $savePath
     * @param $expectedValue
     * @dataProvider dataProviderTestSaveConfig
     */
    public function testSaveConfig($paramName, $paramValue, $savePath, $expectedValue)
    {

        $storeId = 1;

        if (!$savePath) {
            $this->configWriterMock->expects(static::never())->method('save');
        } else {
            $this->configWriterMock->expects(static::once())
                ->method('save')
                ->with(
                    $savePath,
                    $expectedValue,
                    \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                    $storeId
                );
        }

        $this->encryptorMock->expects(static::any())
            ->method('encrypt')
            ->willReturnCallback(function ($arg) {
                return str_rot13($arg);
            });

        $this->configurator->saveConfig([$paramName => $paramValue], $storeId);
    }

    public function dataProviderTestSaveConfig()
    {
        return [
            [
                'paramName' => 'login_id',
                'paramValue' => '12312321',
                'savePath' => 'authorize_net/anet_core/login_id',
                'expectedValue' => str_rot13('12312321'),
            ],
            [
                'paramName' => 'not_supported_param',
                'paramValue' => '12312321',
                'savePath' => false,
                'expectedValue' => str_rot13('12312321'),
            ],
            [
                'paramName' => 'client_key',
                'paramValue' => 'true',
                'savePath' => 'authorize_net/anet_core/client_key',
                'expectedValue' => true,
            ],
            [
                'paramName' => 'client_key',
                'paramValue' => 'false',
                'savePath' => 'authorize_net/anet_core/client_key',
                'expectedValue' => false,
            ],
            [
                'paramName' => 'client_key',
                'paramValue' => [1, 2],
                'savePath' => 'authorize_net/anet_core/client_key',
                'expectedValue' => '1,2',
            ],
        ];
    }
}
