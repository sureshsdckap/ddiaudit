<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Controller\Adminhtml\Merchant;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Controller\Adminhtml\Merchant\Save;

class SaveTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator|MockObject
     */
    protected $configuratorMock;

    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messagesManagerMock;

    /**
     * @var Save
     */
    protected $controller;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();
        $this->resultRedirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $this->messagesManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();

        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects(static::any())->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messagesManagerMock);

        $this->configuratorMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Merchant\Configurator::class)->disableOriginalConstructor()->getMock();

        $this->controller = new Save(
            $this->contextMock,
            $this->configuratorMock
        );
    }

    public function testExecute()
    {
        $this->resultRedirectFactory->expects(static::once())->method('create')->willReturn($this->resultRedirectMock);

        $params = ['some' => 'param'];

        $this->requestMock->method('getParams')->willReturn($params);

        $this->configuratorMock->expects(static::once())->method('saveConfig')->with($params);

        $this->resultRedirectMock->expects(static::once())->method('setPath')->with('admin/system_config/edit/section/payment');

        $this->messagesManagerMock->expects(static::once())->method('addSuccessMessage')->with(__('Configuration has been saved!'));

        static::assertEquals($this->resultRedirectMock, $this->controller->execute());
    }

    public function testExecuteWithLocalizedException()
    {
        $exception = new \Magento\Framework\Exception\LocalizedException(__('Ooops'));

        $this->resultRedirectFactory->expects(static::once())->method('create')->willReturn($this->resultRedirectMock);

        $params = ['some' => 'param'];

        $this->requestMock->method('getParams')->willReturn($params);

        $this->resultRedirectMock->expects(static::once())->method('setPath')->with('admin/system_config/edit/section/payment');

        $this->configuratorMock->expects(static::once())->method('saveConfig')->with($params)->willThrowException($exception);

        $this->messagesManagerMock->expects(static::once())->method('addExceptionMessage')->with($exception);

        static::assertEquals($this->resultRedirectMock, $this->controller->execute());
    }

    public function testExecuteWithException()
    {
        $exception = new \Exception('Ooops');

        $this->resultRedirectFactory->expects(static::once())->method('create')->willReturn($this->resultRedirectMock);

        $params = ['some' => 'param'];

        $this->requestMock->method('getParams')->willReturn($params);

        $this->resultRedirectMock->expects(static::once())->method('setPath')->with('admin/system_config/edit/section/payment');

        $this->configuratorMock->expects(static::once())->method('saveConfig')->with($params)->willThrowException($exception);

        $this->messagesManagerMock->expects(static::once())->method('addExceptionMessage')->with($exception, __('Error while saving configuration.'));

        static::assertEquals($this->resultRedirectMock, $this->controller->execute());
    }
}
