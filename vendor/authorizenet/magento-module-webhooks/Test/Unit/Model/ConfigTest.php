<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $methodCode;

    protected $pathPattern;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */

    protected $storeManagerMock;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */

    protected $storeMock;
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
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->getMockBuilder(\Magento\Store\Model\Store::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptorMock = $this->getMockBuilder(\Magento\Framework\Encryption\EncryptorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->storeManagerMock->expects(static::any())
            ->method('getStore')
            ->willReturn($this->storeMock);

        $this->storeMock->expects(static::any())
            ->method('getId')
            ->willReturn(1);

        $this->scopeConfigMock->expects(static::any())
            ->method('getValue')
            ->willReturn('value');

        $this->config = new Config(
            $this->scopeConfigMock,
            $this->storeManagerMock,
            $this->encryptorMock,
            $this->methodCode,
            $this->pathPattern
        );
    }

    public function testGetRecipientEmail()
    {
        static::assertEquals('value', $this->config->getRecipientEmail());
    }

    public function testGetSender()
    {
        static::assertEquals('value', $this->config->getSender());
    }

    public function testGetEmailTemplate()
    {
        static::assertEquals('value', $this->config->getEmailTemplate());
    }
}
