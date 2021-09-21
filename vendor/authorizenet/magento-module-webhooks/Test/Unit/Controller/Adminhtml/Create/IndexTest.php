<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Controller\Adminhtml\Create;

use AuthorizeNet\Webhooks\Controller\Adminhtml\Create\Index;
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
            ->setMethods(['createWebhooks'])
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
            ->method('createWebhooks')
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
            ->method('createWebhooks')
            ->willThrowException($exception);

        $this->messageManagerMock->expects(static::any())
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
        $data = new \stdClass();
        $name = 'm2_priorAuthCapture_created';
        $data->name = $name;
        return [
            [
                [
                    $name => [
                        'status' => 200,
                        'data' => $data
                    ]
                ],
                __('Webhook %1 was registered successfully.', $data->name),
                'addSuccessMessage'
            ],
            [
                [
                    $name => [
                        'status' => 400,
                        'data' => $data
                    ]
                ],
                __('Webhook %1 was not created', $data->name),
                'addErrorMessage'
            ]
        ];
    }
}
