<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Gateway\Config;

use AuthorizeNet\CreditCard\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;
    /**
     * @var EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;
    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Config $config
     */
    protected $config;

    /**
     *
     */
    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->setMethods(['getStore', 'getId'])
            ->getMockForAbstractClass();
        $this->encryptorMock = $this->getMockBuilder(EncryptorInterface::class)->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(ScopeConfigInterface::class)->getMockForAbstractClass();

        $this->storeManagerMock->expects(static::any())
            ->method('getStore')
            ->willReturnSelf();

        $this->storeManagerMock->expects(static::any())
            ->method('getId')
            ->willReturn(1);

        $this->config = new Config(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->encryptorMock,
            Config::CODE
        );
    }

    /**
     * @param string $value
     * @param array $expected
     * @dataProvider getAvailableCardTypesDataProvider
     */
    public function testGetAvailableCardTypes($value, $expected)
    {
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_CC_TYPES), ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->config->getAvailableCardTypes()
        );
    }

    public function testGetVaultRequireCvv()
    {
        $value = true;
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_REQUIRE_CVV, Config::VAULT_CODE), ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($value);
        static::assertEquals(
            $value,
            $this->config->getVaultRequireCvv()
        );
    }

    public function testGetVaultAdminRequireCvv()
    {
        $value = true;
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_ADMIN_REQUIRE_CVV, Config::VAULT_CODE), ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($value);
        static::assertEquals(
            $value,
            $this->config->getVaultAdminRequireCvv()
        );
    }

    public function testIsCentinelActive()
    {
        $value = true;
        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with($this->getPath(Config::KEY_CENTINEL_ACTIVE), ScopeInterface::SCOPE_STORE, 1)
            ->willReturn($value);
        static::assertEquals(
            $value,
            $this->config->isCentinelActive()
        );
    }

    /**
     * @return array
     */
    public function getAvailableCardTypesDataProvider()
    {
        return [
            [
                'AE,VI,MC,DI,JCB',
                ['AE', 'VI', 'MC', 'DI', 'JCB']
            ],
            [
                '',
                []
            ]
        ];
    }

    /**
     * Return config path
     *
     * @param string $field
     * @return string
     */
    private function getPath($field, $code = Config::CODE)
    {
        return sprintf(Config::DEFAULT_PATH_PATTERN, $code, $field);
    }
}
