<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Controller\Checkout;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Controller\Checkout\SaveShippingMethod;

class SaveShippingMehodTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Action\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout|MockObject
     */
    protected $checkoutMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;
    /**
     * @var \Magento\Framework\App\ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|MockObject
     */
    protected $viewMock;
    /**
     * @var SaveShippingMethod
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|MockObject
     */
    protected $formkeyValidatorMock;


    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Model\Checkout::class)->disableOriginalConstructor()->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->setMethods(['setBody'])
            ->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)->disableOriginalConstructor()->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()->getMock();
        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->disableOriginalConstructor()->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        $this->formkeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)->disableOriginalConstructor()->getMock();

        $this->contextMock->expects(static::any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects(static::any())->method('getRedirect')->willReturn($this->redirectMock);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects(static::any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->contextMock->expects(static::any())->method('getView')->willReturn($this->viewMock);

        $this->viewMock->expects(static::any())->method('getLayout')->willReturn($this->layoutMock);

        $this->controller = new SaveShippingMethod(
            $this->contextMock,
            $this->checkoutMock,
            $this->formkeyValidatorMock
        );
    }

    public function testExecute()
    {

        $methodName = 'some_method';
        $isAjax = false;

        $this->formkeyValidatorMock->expects(static::once())->method('validate')->with($this->requestMock)->willReturn(true);

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap([
            ['isAjax', null, $isAjax],
            ['shipping_method', null, $methodName]
        ]);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->with($methodName);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }


    public function testExecuteAjax()
    {

        $methodName = 'some_method';
        $htmlOutput = 'some output blah-blah';
        $isAjax = true;

        $this->formkeyValidatorMock->expects(static::once())->method('validate')->with($this->requestMock)->willReturn(true);

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)->getMockForAbstractClass();
        $blockMock->expects(static::once())->method('toHtml')->willReturn($htmlOutput);

        $this->responseMock->expects(static::any())->method('setBody')->with($htmlOutput)->willReturnSelf();

        $this->viewMock->expects(static::once())->method('loadLayout')->with('anet_paypal_express_review_details', true, true, false);

        $this->layoutMock->expects(static::once())->method('getBlock')->with('page.block')->willReturn($blockMock);

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap([
            ['isAjax', null, $isAjax],
            ['shipping_method', null, $methodName]
        ]);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->with($methodName);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function testExecuteValidatorFails()
    {

        $this->formkeyValidatorMock->expects(static::any())->method('validate')->with($this->requestMock)->willReturn(false);

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap(
            [
                ['isAjax', null, false],
            ]
        );

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with(new \Magento\Framework\Exception\LocalizedException(__('Invalid form key')), 'Invalid form key');

        $this->checkoutMock->expects(static::never())->method('updateShippingMethod');
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, '*/*/review', []);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }


    /**
     * @dataProvider dataProviderTestExecuteWithException
     */
    public function testExecuteWithException($expectedException, $expectedErrorMessage)
    {
        $this->formkeyValidatorMock->expects(static::any())->method('validate')->willReturn(true);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->willThrowException($expectedException);
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, '*/*/review', []);

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with($expectedException, $expectedErrorMessage);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function dataProviderTestExecuteWithException()
    {
        return [
            [
                'expectedException' => new \Magento\Framework\Exception\LocalizedException(__('Some')),
                'expectedErrorMessage' => 'Some',
            ],
            [
                'expectedException' => new \Exception('Some'),
                'expectedErrorMessage' => 'We can\'t update shipping method.',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestExecuteWithException
     */
    public function testExecuteWithExceptionAjax($expectedException, $expectedErrorMessage)
    {

        $this->formkeyValidatorMock->expects(static::any())->method('validate')->willReturn(true);

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap([
            ['isAjax', null, true],
            ['shipping_method', null, 'someMethod']
        ]);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->willThrowException($expectedException);

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with($expectedException, $expectedErrorMessage);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }
}
