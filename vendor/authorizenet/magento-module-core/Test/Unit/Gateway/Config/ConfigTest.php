<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

class ConfigTest extends \PHPUnit\Framework\TestCase
{


    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $encryptorMock;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var Config
     */
    protected $config;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMockBuilder(\Magento\Store\Model\StoreManagerInterface::class)
            ->setMethods(['getStore', 'getId'])
            ->getMockForAbstractClass();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)->getMockForAbstractClass();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMockForAbstractClass();

        $this->config = new Config(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->encryptorMock
        );
    }

    /**
     * @param $methodName
     * @param $expectedValue
     * @param $field
     * @dataProvider dataProviderTestConfig
     */
    public function testConfig($methodName, $expectedValue, $field, $pathPattern, $methodCode)
    {
        $this->config->setMethodCode($methodCode);
        $this->config->setPathPattern($pathPattern);

        $this->encryptorMock->expects(static::any())
            ->method('decrypt')
            ->willReturnArgument(0);

        $storeId = 0;

        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturnSelf();

        $this->storeManagerMock->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                sprintf($pathPattern, $methodCode, $field),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->config->{$methodName}());
    }

    public function dataProviderTestConfig()
    {
        return [
            [
                'methodName' => 'isActive',
                'expectedValue' => true,
                'field' => 'active',
                'pathPattern' => 'payment/%s/%s',
                'methodCode' => 'some_method',
            ],
            [
                'methodName' => 'getLoginId',
                'expectedValue' => '12313123',
                'field' => 'login_id',
                'pathPattern' => 'authorize_net/%s/%s',
                'methodCode' => 'anet_core',
            ],
            [
                'methodName' => 'getTransKey',
                'expectedValue' => '24g33qb3b',
                'field' => 'trans_key',
                'pathPattern' => 'authorize_net/%s/%s',
                'methodCode' => 'anet_core',
            ],
            [
                'methodName' => 'getClientKey',
                'expectedValue' => '3343434g34',
                'field' => 'client_key',
                'pathPattern' => 'authorize_net/%s/%s',
                'methodCode' => 'anet_core',
            ],
            [
                'methodName' => 'getTitle',
                'expectedValue' => 'My Method title',
                'field' => 'title',
                'pathPattern' => 'payment/%s/%s',
                'methodCode' => 'some_method',
            ],
            [
                'methodName' => 'getSpecificCountry',
                'expectedValue' => ['US'],
                'field' => 'specificcountry',
                'pathPattern' => 'payment/%s/%s',
                'methodCode' => 'some_method',
            ],
            [
                'methodName' => 'getSignatureKey',
                'expectedValue' => 'mysigkey',
                'field' => 'signature_key',
                'pathPattern' => 'authorize_net/%s/%s',
                'methodCode' => 'anet_core',
            ],
        ];
    }

    public function dataProviderTestSolutionId()
    {
        return [
            ['testMode' => true, 'expectedValue' => \AuthorizeNet\Core\Gateway\Config\Config::TEST_SOLUTION_ID],
            ['testMode' => false, 'expectedValue' => \AuthorizeNet\Core\Gateway\Config\Config::PROD_SOLUTION_ID],
        ];
    }

    /**
     * @param $debugMode
     * @param $expectedValue
     * @dataProvider dataProviderTestDebugOn
     */
    public function testIsDebugOn($debugMode, $expectedValue)
    {
        $methodCode = 'some_method';
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN;

        $this->config->setMethodCode($methodCode);
        $this->config->setPathPattern($pathPattern);

        $this->encryptorMock->expects(static::any())
            ->method('decrypt')
            ->willReturnArgument(0);

        $storeId = 0;

        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturnSelf();

        $this->storeManagerMock->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                sprintf($pathPattern, 'anet_core', 'debug'),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn($debugMode);

        static::assertEquals($expectedValue, $this->config->isDebugOn());
    }

    /**
     * @param $sandboxMode
     * @param $expectedValue
     * @dataProvider dataProviderTestSandboxMode
     */
    public function testIsSandboxMode($sandboxMode, $expectedValue)
    {
        $methodCode = 'some_method';
        $pathPattern = \Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN;

        $this->config->setMethodCode($methodCode);
        $this->config->setPathPattern($pathPattern);

        $this->encryptorMock->expects(static::any())
            ->method('decrypt')
            ->willReturnArgument(0);

        $storeId = 0;

        $this->storeManagerMock->expects(static::once())
            ->method('getStore')
            ->willReturnSelf();

        $this->storeManagerMock->expects(static::once())
            ->method('getId')
            ->willReturn($storeId);

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with(
                sprintf($pathPattern, 'anet_core', 'test_mode'),
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
            ->willReturn($sandboxMode);

        static::assertEquals($expectedValue, $this->config->isTestMode());
    }

    public function testSandboxMode()
    {
        $this->config->setSandboxMode(true);
        $sandboxMode = $this->config->getSandboxMode();
        static::assertEquals($sandboxMode, $this->config->isTestMode());
    }

    /**
     * @param $sandboxMode
     * @param $expectedValue
     * @dataProvider dataProviderTestGetSolutionId
     */
    public function testGetSolutionId($testMode, $expectedValue)
    {
        $this->config->setSandboxMode($testMode);
        static::assertEquals($expectedValue, $this->config->getSolutionId());
    }

    public function dataProviderTestDebugOn()
    {
        return [
            ['debugMode' => '1', 'expectedValue' => true],
            ['debugMode' => '0', 'expectedValue' => false],
        ];
    }

    public function dataProviderTestGetSolutionId()
    {
        return [
            ['testMode' => true, 'expectedValue' => Config::TEST_SOLUTION_ID],
            ['testMode' => false, 'expectedValue' => Config::PROD_SOLUTION_ID],
        ];
    }

    public function dataProviderTestSandboxMode()
    {
        return [
            ['sandboxMode' => '1', 'expectedValue' => true],
            ['sandboxMode' => '0', 'expectedValue' => false],
        ];
    }
}
