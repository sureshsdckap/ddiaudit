<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Controller\Adminhtml\Delete;

use AuthorizeNet\Webhooks\Controller\Adminhtml\Delete\Index;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    /**
     * @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Webhooks|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $modelMock;
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactoryMock;
    /**
     * @var \Magento\Framework\Controller\Result\Redirect|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;
    /**
     * @var Index
     */
    protected $controller;

    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMessageManager', 'getResultRedirectFactory'])
            ->getMock();

        $this->modelMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Webhooks::class)
            ->disableOriginalConstructor()
            ->setMethods(['deleteWebhooks'])
            ->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMockForAbstractClass();

        $this->resultRedirectFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();

        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();

        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects(static::any())->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactoryMock);

        $this->resultRedirectFactoryMock->expects(static::any())
            ->method('create')
            ->willReturn($this->redirectMock);

        $this->redirectMock->expects(static::any())
            ->method('setPath')
            ->with('*/status/index')
            ->willReturnSelf();


        $this->controller = new Index(
            $this->contextMock,
            $this->modelMock
        );
    }

    /**
     * @dataProvider testExecuteDataProvider
     */
    public function testExecute($value, $message, $method)
    {
        $this->modelMock->expects(static::any())
            ->method('deleteWebhooks')
            ->willReturn($value);

        $this->messageManagerMock->expects(static::once())
            ->method($method)
            ->with($message)
            ->willReturnSelf();

        static::assertEquals($this->redirectMock, $this->controller->execute());
    }

    public function testException()
    {
        $message = 'Something went wrong.';
        $exception = new \Exception($message);
        $this->modelMock->expects(static::any())
            ->method('deleteWebhooks')
            ->willThrowException($exception);

        $this->messageManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with(static::isInstanceOf(get_class($exception)), $message)
            ->willReturnSelf();

        static::assertEquals($this->redirectMock, $this->controller->execute());
    }

    /**
     * @return array
     */
    public function testExecuteDataProvider()
    {
        $name = 'm2_priorAuthCapture_created';
        return [
            [
                [],
                __('No magento webhooks found.'),
                'addNoticeMessage'
            ],
            [
                [
                    $name => [
                        'status' => 200,
                        'data' => null
                    ]
                ],
                __('Webhook %1 was unregistered successfully.', $name),
                'addSuccessMessage'
            ]
        ];
    }
}
