<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Block;

use PHPUnit\Framework\TestCase;
use Magento\Backend\Block\Template\Context;
use AuthorizeNet\Core\Gateway\Config\Config;

class AcceptJsTest extends TestCase
{

    /* @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;
    
    /* @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $contextMock;
    
    /* @var \AuthorizeNet\Core\Block\AcceptJs|\PHPUnit_Framework_MockObject_MockObject */
    protected $model;
    
    protected $methodCode;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(Context::class)->disableOriginalConstructor()->getMock();

        $this->methodCode = 'blahblah';
        
        $this->model = new AcceptJs(
            $this->contextMock,
            $this->configMock,
            []
        );
    }

    /**
     * @covers \AuthorizeNet\Core\Block\AcceptJs::__construct
     */
    public function testConfigMethodCode()
    {
        
        $this->configMock->expects(static::once())
            ->method('setMethodCode')
            ->with($this->methodCode)
            ->willReturnSelf();

        $this->model = new AcceptJs(
            $this->contextMock,
            $this->configMock,
            ['method_code' => $this->methodCode]
        );
    }

    /**
     * @covers \AuthorizeNet\Core\Block\AcceptJs::getAcceptJsUrl
     */
    public function testGetAcceptJsUrl()
    {
        $this->configMock
            ->expects(self::exactly(2))
            ->method('isTestMode')
            ->will(self::onConsecutiveCalls(false, true));
        
        self::assertEquals(AcceptJs::ACCEPT_JS_PROD_URL, $this->model->getAcceptJsUrl());
        self::assertEquals(AcceptJs::ACCEPT_JS_TEST_URL, $this->model->getAcceptJsUrl());
    }
}
