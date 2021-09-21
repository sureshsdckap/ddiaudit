<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Block;

use PHPUnit\Framework\TestCase;

class JsTest extends TestCase
{


    /**
     * @var \Magento\Backend\Block\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    
    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    
    /**
     * @var Js
     */
    protected $block;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\Block\Template\Context::class)->disableOriginalConstructor()->getMock();
        
        $this->block = new Js(
            $this->contextMock,
            $this->configMock
        );
    }

    /**
     * @param $testMode
     * @dataProvider dataProviderIsSandbox
     */
    public function testIsSandbox($testMode)
    {
        $this->configMock->expects(static::once())
            ->method('isTestMode')
            ->willReturn($testMode);
        
        static::assertEquals($testMode, $this->block->isSandbox());
    }

    public function dataProviderIsSandbox()
    {
        return [
            ['testMode' => true],
            ['testMode' => false],
        ];
    }
}
