<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Controller\Adminhtml\Payload;

use AuthorizeNet\Webhooks\Controller\Adminhtml\Payload\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \Magento\Framework\View\Result\PageFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageFactoryMock;
    /**
     * @var \Magento\Framework\View\Result\Page|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultPageMock;
    /**
     * @var \Magento\Framework\View\Page\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageConfigMock;
    /**
     * @var \Magento\Framework\View\Page\Title|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $pageTitle;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageFactoryMock = $this->getMockBuilder(\Magento\Framework\View\Result\PageFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig'])
            ->getMock();

        $this->pageConfigMock = $this->getMockBuilder(\Magento\Framework\View\Page\Config::class)
            ->disableOriginalConstructor()
            ->setMethods(['getTitle'])
            ->getMock();

        $this->pageTitle = $this->getMockBuilder(\Magento\Framework\View\Page\Title::class)
            ->disableOriginalConstructor()
            ->setMethods(['prepend'])
            ->getMock();

        $this->resultPageFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->resultPageMock);

        $this->resultPageMock->expects(static::any())
            ->method('getConfig')
            ->willReturn($this->pageConfigMock);

        $this->pageConfigMock->expects(static::any())
            ->method('getTitle')
            ->willReturn($this->pageTitle);

        $this->pageTitle->expects(static::any())
            ->method('prepend')
            ->willReturn($this->pageConfigMock);

        $this->controller = new Index(
            $this->contextMock,
            $this->resultPageFactoryMock
        );
    }

    public function testExecute()
    {
        $this->pageTitle->expects(static::any())
            ->method('prepend')
            ->with(__('Webhook Payloads'))
            ->willReturnSelf();

        static::assertEquals($this->resultPageMock, $this->controller->execute());
    }
}
