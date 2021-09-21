<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Controller\Checkout;

use AuthorizeNet\VisaCheckout\Controller\Checkout\SaveShippingMethod;

class SaveShippingMethodTest extends AbstractControllerTest
{

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new SaveShippingMethod(
            $this->contextMock,
            $this->checkoutMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->formkeyValidatorMock
        );
    }

    public function testEmptyCart()
    {

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with(
                static::isInstanceOf('\Magento\Framework\Exception\LocalizedException'),
                'We can\'t initialize Visa Checkout review.'
            );

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function testGenericException()
    {
        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->prepareCartItems();

        $exception = new \Exception('Some unpredictable stuff happened');

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->willThrowException($exception);

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with($exception, 'We can\'t update shipping method.');

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function testExecuteAjaxException()
    {

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->prepareCartItems();

        $exception = new \Exception('Some unpredictable stuff happened');

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, true],
                ['shipping_method', null, 'some_method'],
            ]);
        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->willThrowException($exception);

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with($exception, 'We can\'t update shipping method.');

        $redirectUrl = 'http://example.org/visacheckout/review';

        static::assertEquals($this->responseMock, $this->controller->execute());
    }


    public function testExecute()
    {

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->prepareCartItems();

        $method = 'some_shipping_method';

        $isAjax = false;

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, $isAjax],
                ['shipping_method', null, $method],
            ]);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->with($method);

        $redirectPath = '*/*/review';

        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, $redirectPath, []);

        static::assertInstanceOf('\Magento\Framework\App\ResponseInterface', $this->controller->execute());
    }

    public function testExecuteInvalidFormkey()
    {

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $exception = new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with($exception, $exception->getMessage());

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function testExecuteAjax()
    {

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->prepareCartItems();

        $method = 'some_shipping_method';

        $isAjax = true;

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['isAjax', null, $isAjax],
                ['shipping_method', null, $method],
            ]);

        $this->checkoutMock->expects(static::once())->method('updateShippingMethod')->with($method);

        $this->viewMock->expects(static::once())
            ->method('loadLayout')
            ->with('authorizenetvisa_review_details', true, true, false);

        $responseText = '<some><html>Code</html></some>';

        $blockMock = $this->getMockBuilder(\Magento\Framework\View\Element\BlockInterface::class)->getMockForAbstractClass();
        $blockMock->expects(static::any())->method('toHtml')->willReturn($responseText);

        $this->layoutMock->expects(static::any())->method('getBlock')->with('page.block')->willReturn($blockMock);

        $this->responseMock->expects(static::once())->method('setBody')->with($responseText)->willReturnSelf();

        static::assertInstanceOf('\Magento\Framework\App\ResponseInterface', $this->controller->execute());
    }
}
