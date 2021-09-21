<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Controller\Adminhtml\Merchant;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Controller\Adminhtml\Merchant\GetDetails;

class GetDetailsTest extends TestCase
{

    /**
     * @var \Magento\Backend\App\Action\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonFactoryMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $jsonMock;

    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator|MockObject
     */
    protected $configuratorMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|MockObject
     */
    protected $configMock;

    /**
     * @var GetDetails
     */
    protected $controller;


    protected function setUp()
    {
        $this->contextMock = $this->getMockBuilder(\Magento\Backend\App\Action\Context::class)->disableOriginalConstructor()->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();

        $this->contextMock->expects(static::any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->jsonMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)->disableOriginalConstructor()->getMock();
        $this->jsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)->disableOriginalConstructor()->getMock();
        $this->jsonFactoryMock->expects(static::any())->method('create')->willReturn($this->jsonMock);

        $this->configuratorMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Merchant\Configurator::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->controller = new GetDetails(
            $this->contextMock,
            $this->jsonFactoryMock,
            $this->configuratorMock,
            $this->configMock
        );
    }

    public function testExecute()
    {
        $sandboxMode = 'true';

        $credentials = [
            'login_id' => '12d1d1d',
            'trans_key' => 'asdqf12f',
        ];

        $details = ['some' => 'data'];

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['login_id', null, $credentials['login_id']],
                ['transaction_key', null, $credentials['trans_key']],
                ['sandbox_mode', null, $sandboxMode],
            ]);

        $this->configuratorMock->expects(static::once())
            ->method('loadConfig')
            ->with($credentials['login_id'], $credentials['trans_key'])
            ->willReturn($details);

        $this->configMock->expects(static::once())->method('setSandboxMode')->with(true);

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(
                ['status' => true, 'details' => $details]
            );

        static::assertEquals($this->jsonMock, $this->controller->execute());
    }


    public function testExecuteMaskedValues()
    {

        $credentials = [
            'login_id' => '12d1d1d',
            'trans_key' => 'asdqf12f',
        ];

        $details = ['some' => 'data'];

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['login_id', null, $credentials['login_id']],
                ['transaction_key', null, \AuthorizeNet\Core\Model\Merchant\DataProvider::MASKED_VALUE],
            ]);

        $this->configMock->expects(static::once())->method('getTransKey')->willReturn($credentials['trans_key']);

        $this->configuratorMock->expects(static::once())
            ->method('loadConfig')
            ->with($credentials['login_id'], $credentials['trans_key'])
            ->willReturn($details);

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(
                ['status' => true, 'details' => $details]
            );

        static::assertEquals($this->jsonMock, $this->controller->execute());
    }

    /**
     * @param $exception
     * @param $expectedMessage
     * @dataProvider dataProviderTestExecuteWithException
     */
    public function testExecuteWithException($exception, $expectedMessage)
    {

        $credentials = [
            'login_id' => '12d1d1d',
            'trans_key' => 'asdqf12f',
        ];

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['login_id', null, $credentials['login_id']],
                ['transaction_key', null, \AuthorizeNet\Core\Model\Merchant\DataProvider::MASKED_VALUE],
            ]);

        $this->configMock->expects(static::once())->method('getTransKey')->willReturn($credentials['trans_key']);

        $this->configuratorMock->expects(static::once())
            ->method('loadConfig')
            ->with($credentials['login_id'], $credentials['trans_key'])
            ->willThrowException($exception);

        $this->jsonMock->expects(static::once())
            ->method('setData')
            ->with(
                ['status' => false, 'message' => $expectedMessage]
            );

        static::assertEquals($this->jsonMock, $this->controller->execute());
    }

    public function dataProviderTestExecuteWithException()
    {
        return [
            [
                'exception' => new \Magento\Framework\Exception\LocalizedException(__('Ouch!')),
                'expectedMessage' => __('Ouch!')
            ],
            [
                'exception' => new \Exception('Oops!'),
                'expectedMessage' => __('Unable to load configuration'),
            ],
        ];
    }
}
