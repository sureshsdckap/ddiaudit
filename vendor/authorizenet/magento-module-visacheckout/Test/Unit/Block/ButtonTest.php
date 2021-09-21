<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Block;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\VisaCheckout\Block\Button;

class ButtonTest extends TestCase
{

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    
    /**
     * @var \Magento\Framework\Math\Random|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $randomMock;
    
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;


    /**
     * @var Button
     */
    protected $buttonBlock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->randomMock = $this->getMockBuilder(\Magento\Framework\Math\Random::class)->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)->disableOriginalConstructor()->getMock();
        
        $this->buttonBlock = new Button(
            $this->configMock,
            $this->randomMock,
            $this->contextMock
        );
    }


    public function testGetAlias()
    {
        static::assertEquals('product.info.addtocart.visa_checkout', $this->buttonBlock->getAlias());
    }


    /**
     * @param $isActive
     * @dataProvider dataProviderTestIsActive
     */
    public function testIsActive($isActive)
    {
        $this->configMock->expects(static::once())
            ->method('isActive')
            ->willReturn($isActive);
        
        static::assertEquals($isActive, $this->buttonBlock->isActive());
    }

    public function dataProviderTestIsActive()
    {
        return [
            ['isActive' => true],
            ['isActive' => false],
        ];
    }

    /**
     * @param $testMode
     * @param $expectedUrl
     * @dataProvider dataProviderTestGetButtonImageUrl
     */
    public function testGetButtonImageUrl($testMode, $expectedUrl)
    {
        $this->configMock->expects(static::once())
            ->method('isTestMode')
            ->willReturn($testMode);
        
        static::assertEquals($expectedUrl, $this->buttonBlock->getButtonImageUrl());
    }

    public function dataProviderTestGetButtonImageUrl()
    {
        return [
            ['testMode' => true, 'expectedUrl' => \AuthorizeNet\VisaCheckout\Block\Button::SANDBOX_BUTTON_URL],
            ['testMode' => false, 'expectedUrl' => \AuthorizeNet\VisaCheckout\Block\Button::LIVE_BUTTON_URL],
        ];
    }

    public function testGetApiKey()
    {
        $key = 'mysomekey';

        $this->configMock->expects(static::once())
            ->method('getApiKey')
            ->willReturn($key);

        static::assertEquals($key, $this->buttonBlock->getApiKey());
    }

    public function testBeforeToHtml()
    {
        
        $prefix = 'vc_button_';
        $pseudoRandom = 'piomj4vpp34v34pb3b';

        $this->randomMock->expects(static::once())
            ->method('getUniqueHash')
            ->willReturnCallback(
                function ($arg) use ($pseudoRandom) {
                    return $arg . $pseudoRandom;
                }
            );

        $testHelper = new ButtonTestHelper(
            $this->configMock,
            $this->randomMock,
            $this->contextMock
        );
        
        $testHelper->___beforeToHtml();
        
        static::assertEquals($prefix . $pseudoRandom, $testHelper->getShortcutHtmlId());
    }
    
    public function testToHtmlInactive()
    {
        $this->configMock->expects(static::once())
            ->method('isActive')
            ->willReturn(false);

        $testHelper = new ButtonTestHelper(
            $this->configMock,
            $this->randomMock,
            $this->contextMock
        );
        
        static::assertEquals('', $testHelper->___toHtml());
    }


    /**
     * @expectedException \Magento\Framework\Exception\ValidatorException
     */
    public function testToHtmlActive()
    {
        
        // make sure that parent's _toHtml is called with non-valid template and exception is thrown
        
        $this->configMock->expects(static::once())
            ->method('isActive')
            ->willReturn(true);
        
        $appStateMock = $this->getMockBuilder(\Magento\Framework\App\State::class)->disableOriginalConstructor()->getMock();
        $resolverMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\File\Resolver::class)->disableOriginalConstructor()->getMock();
        
        $resolverMock->expects(static::once())
            ->method('getTemplateFileName')
            ->willThrowException(new \Magento\Framework\Exception\ValidatorException(__('Could not find a template')));

        $this->contextMock->expects(static::any())
            ->method('getAppState')
            ->willReturn($appStateMock);

        $this->contextMock->expects(static::any())
            ->method('getResolver')
            ->willReturn($resolverMock);
        
        $testHelper = new ButtonTestHelper(
            $this->configMock,
            $this->randomMock,
            $this->contextMock
        );
        
        $testHelper->setTemplate('non-existent-template.phtml');

        $testHelper->___toHtml();
    }
}
