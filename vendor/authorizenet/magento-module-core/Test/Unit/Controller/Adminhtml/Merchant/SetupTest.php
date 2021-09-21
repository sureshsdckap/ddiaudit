<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Controller\Adminhtml\Merchant;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Controller\Adminhtml\Merchant\Setup;

class SetupTest extends TestCase
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory|MockObject
     */
    protected $pageFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|MockObject
     */
    protected $pageMock;

    /**
     * @var \Magento\Backend\App\Action\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Setup
     */
    protected $controller;

    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->pageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)->disableOriginalConstructor()->getMock();

        $this->controller = new Setup(
            $this->contextMock,
            $this->pageFactoryMock
        );
    }

    public function testExecute()
    {

        $this->pageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->setMethods(['getConfig', 'getTitle', 'prepend'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->pageMock->expects(static::once())->method('getConfig')->willReturnSelf();
        $this->pageMock->expects(static::once())->method('getTitle')->willReturnSelf();
        $this->pageMock->expects(static::once())->method('prepend')->with(__('Merchant Setup Wizard'));

        $this->pageFactoryMock->expects(static::once())->method('create')->willReturn($this->pageMock);

        static::assertEquals($this->pageMock, $this->controller->execute());
    }
}
