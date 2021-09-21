<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Observer;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Observer\AddShortcuts;

class AddShortcutsTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $gatewayConfigMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Framework\Event|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventMock;

    /**
     * @var \Magento\Catalog\Block\ShortcutButtons|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $containerMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Block\Button|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shortcutMock;
    
    /**
     * @var AddShortcuts
     */
    protected $observer;

    protected $blockClass = 'MySomeClass';

    public function setUp()
    {
        $this->gatewayConfigMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface::class)->disableOriginalConstructor()->getMockForAbstractClass();
        $this->eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->disableOriginalConstructor()->getMock();
        
        $this->eventMock = $this->getMockBuilder(\Magento\Framework\Event::class)
            ->setMethods(['getContainer', 'getIsCatalogProduct'])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->containerMock = $this->getMockBuilder(\Magento\Catalog\Block\ShortcutButtons::class)->disableOriginalConstructor()->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        
        $this->shortcutMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Block\Button::class)
            ->disableOriginalConstructor()
            ->setMethods(['setIsCatalogProduct'])
            ->getMock();
        
        $this->containerMock->expects(static::any())
            ->method('getLayout')
            ->willReturn($this->layoutMock);
        
        $this->eventMock->expects(static::any())
            ->method('getContainer')
            ->willReturn($this->containerMock);
        
        $this->eventObserverMock->expects(static::any())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->observer = new AddShortcuts(
            $this->gatewayConfigMock,
            $this->blockClass
        );
    }

    /**
     * @param $isCatalogProduct
     * @param $isButtonEnabledOnProduct
     * @param $isButtonEnabledOnCart
     * @dataProvider dataProviderTestExecute
     */
    public function testExecute($isCatalogProduct, $isButtonEnabledOnProduct, $isButtonEnabledOnCart, $expectedCount)
    {
       
        $this->eventMock->expects(static::any())
            ->method('getIsCatalogProduct')
            ->willReturn($isCatalogProduct);
        
        $this->gatewayConfigMock->expects(static::any())
            ->method('isButtonEnabledOnProduct')
            ->willReturn($isButtonEnabledOnProduct);

        $this->gatewayConfigMock->expects(static::any())
            ->method('isButtonEnabledOnCart')
            ->willReturn($isButtonEnabledOnCart);
        
        $this->layoutMock->expects(static::exactly($expectedCount))
            ->method('createBlock')
            ->with($this->blockClass)
            ->willReturn($this->shortcutMock);
        
        $this->shortcutMock->expects(static::exactly($expectedCount))
            ->method('setIsCatalogProduct')
            ->with($isCatalogProduct);
        
        $this->containerMock->expects(static::exactly($expectedCount))
            ->method('addShortcut')
            ->with($this->shortcutMock);
        
        $this->observer->execute($this->eventObserverMock);
    }
    
    public function dataProviderTestExecute()
    {
        return [
            ['isCatalogProduct' => true, 'isButtonEnabledOnProduct' => true, 'isButtonEnabledOnCart' => true, 'expectedCount' => 1],
            ['isCatalogProduct' => true, 'isButtonEnabledOnProduct' => false, 'isButtonEnabledOnCart' => true, 'expectedCount' => 0],
            ['isCatalogProduct' => false, 'isButtonEnabledOnProduct' => true, 'isButtonEnabledOnCart' => false, 'expectedCount' => 0],
        ];
    }
}
