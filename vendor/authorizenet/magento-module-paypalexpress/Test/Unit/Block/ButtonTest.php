<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\Core\Test\Unit\AuthorizeNet\PayPalExpress\Block;

use AuthorizeNet\PayPalExpress\Block\Button;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
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
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Button
     */
    protected $buttonBlock;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->randomMock = $this->getMockBuilder(\Magento\Framework\Math\Random::class)->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)->disableOriginalConstructor()->getMock();

        $this->contextMock->expects(static::any())->method('getUrlBuilder')->willReturn($this->urlBuilderMock);

        $this->buttonBlock = new Button(
            $this->contextMock,
            $this->configMock,
            $this->randomMock
        );
    }


    public function testGetAlias()
    {
        static::assertEquals('product.info.addtocart.authorizenet_paypal_checkout', $this->buttonBlock->getAlias());
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

    public function testBeforeToHtml()
    {

        $prefix = 'anet_pp_button_';
        $pseudoRandom = 'piomj4vpp34v34pb3b';

        $this->randomMock->expects(static::once())
            ->method('getUniqueHash')
            ->willReturnCallback(
                function ($arg) use ($pseudoRandom) {
                    return $arg . $pseudoRandom;
                }
            );

        $testHelper = new \AuthorizeNet\PayPalExpress\Test\Unit\Block\ButtonTestHelper(
            $this->contextMock,
            $this->configMock,
            $this->randomMock
        );

        $testHelper->___beforeToHtml();

        static::assertEquals($prefix . $pseudoRandom, $testHelper->getShortcutHtmlId());
    }

    public function testToHtmlInactive()
    {
        $this->configMock->expects(static::once())
            ->method('isActive')
            ->willReturn(false);

        $testHelper = new \AuthorizeNet\PayPalExpress\Test\Unit\Block\ButtonTestHelper(
            $this->contextMock,
            $this->configMock,
            $this->randomMock
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

        $testHelper = new \AuthorizeNet\PayPalExpress\Test\Unit\Block\ButtonTestHelper(
            $this->contextMock,
            $this->configMock,
            $this->randomMock
        );

        $testHelper->setTemplate('non-existent-template.phtml');

        $testHelper->___toHtml();
    }

    /**
     * @param $expectedValue
     * @dataProvider dataProviderTestGetJsonConfig
     */
    public function testGetJsonConfig($expectedValue, $htmlId)
    {
        $this->buttonBlock->setShortcutHtmlId($htmlId);
        $this->buttonBlock->setIsCatalogProduct($expectedValue['isCatalogProduct']);
        $this->buttonBlock->setData('button_label', $expectedValue['buttonLabel']);

        $this->configMock->expects(static::any())->method('isTestMode')->willReturn($expectedValue['isSandbox']);

        $this->urlBuilderMock->expects(static::any())
            ->method('getUrl')
            ->willReturnMap([
                ['anet_paypal_express/checkout/initialize', null, $expectedValue['initActionUrl']],
                ['anet_paypal_express/checkout/review', null, $expectedValue['reviewUrl']],
                ['anet_paypal_express/checkout/saveToken', null, $expectedValue['saveTokenUrl']],
            ]);

        static::assertEquals($expectedValue, json_decode($this->buttonBlock->getJsonConfig(), true));
    }


    public function dataProviderTestGetJsonConfig()
    {
        return [
            [
                'expectedValue' => [
                    'blockContainerSelector' => '#someId',
                    'isCatalogProduct' => true,
                    'isSandbox' => true,
                    'initActionUrl' => 'asdasd',
                    'reviewUrl' => 'asdasd',
                    'saveTokenUrl' => 'asdasd',
                    'ignoreShippingAddress' => 1,
                    'buttonLabel' => 'pay',
                ],
                'htmlId' => 'someId',
                'isCatalogProduct' => true,
                'expected'
            ],
        ];
    }
}
