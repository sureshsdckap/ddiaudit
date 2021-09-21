<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Test\Unit\Model;

use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Centinel\Model\Config;

class ConfigTest extends TestCase
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|MockObject
     */
    protected $storeManagerMock;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;
    /**
     * @var \Magento\Framework\Encryption\Encryptor|MockObject
     */
    protected $encryptorMock;

    /**
     * @var Config
     */
    protected $config;

    protected $storeId = '22';

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)->setMethods([
            'getStore',
            'getId'
        ])->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMockForAbstractClass();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\Encryptor::class)->disableOriginalConstructor()->getMock();

        $this->storeManagerMock->expects(static::any())->method('getStore')->willReturnSelf();
        $this->storeManagerMock->expects(static::any())->method('getId')->willReturn($this->storeId);

        $this->config = new Config(
            $this->storeManagerMock,
            $this->scopeConfigMock,
            $this->encryptorMock
        );
    }

    public function testGetApiId()
    {

        $expectedValue = '123';

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(Config::KEY_API_ID, ScopeInterface::SCOPE_STORE, $this->storeId)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->config->getApiId());
    }

    public function testIsTestMode()
    {

        $expectedValue = '323';

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(Config::KEY_TEST_MODE, ScopeInterface::SCOPE_STORE, $this->storeId)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->config->isTestMode());
    }

    public function testGetUnitId()
    {

        $expectedValue = '443';

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(Config::KEY_UNIT_ID, ScopeInterface::SCOPE_STORE, $this->storeId)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->config->getUnitId());
    }

    public function testGetApiKey()
    {

        $expectedValue = '423';

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(Config::KEY_API_KEY, ScopeInterface::SCOPE_STORE, $this->storeId)
            ->willReturn($expectedValue);

        $this->encryptorMock->expects(static::once())->method('decrypt')->willReturnArgument(0);

        static::assertEquals($expectedValue, $this->config->getApiKey());
    }
}
