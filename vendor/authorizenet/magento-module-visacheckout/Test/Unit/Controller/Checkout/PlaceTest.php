<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Controller\Checkout;

use AuthorizeNet\VisaCheckout\Controller\Checkout\Place;

class PlaceTest extends AbstractControllerTest
{

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $agreementValidatorMock;

    protected function setUp()
    {
        parent::setUp();

        $this->agreementValidatorMock = $this->getMockBuilder(\Magento\Checkout\Api\AgreementsValidatorInterface::class)->getMockForAbstractClass();

        $this->agreementValidatorMock->expects(static::any())->method('isValid')->willReturn(true);

        $this->controller = new Place(
            $this->contextMock,
            $this->checkoutMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->formkeyValidatorMock,
            $this->agreementValidatorMock
        );
    }
    
    public function testExecuteInvalidFormkey()
    {
        
        $redirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        
        $this->resultRedirectFactory->expects(static::once())
            ->method('create')
            ->willReturn($redirect);
        
        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);
        
        $redirect->expects(static::once())
            ->method('setPath')
            ->with('*/*/')
            ->willReturnSelf();
        
        static::assertEquals($redirect, $this->controller->execute());
    }
    
    public function testExecute()
    {
        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        
        $this->prepareCartItems();

        $addressString = 'city=newYork&street=1mainstreet';
        $addressData = [
            'city' => 'newYork',
            'street' => '1mainstreet'
        ];
        
        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['shipping_address', null, $addressString],
                ['billing_address', null, $addressString],
                ['agreement', [], []]
            ]);
        
        $this->checkoutMock->expects(static::once())
            ->method('updateShippingAddressData')
            ->with($addressData)
            ->willReturnSelf();
        
        $this->checkoutMock->expects(static::once())
            ->method('updateBillingAddressData')
            ->with($addressData)
            ->willReturnSelf();
        
        $this->checkoutMock->expects(static::once())
            ->method('place')
            ->willReturnSelf();
        
        $this->redirectMock->expects(static::once())
            ->method('redirect')
            ->with($this->responseMock, 'checkout/onepage/success');
        
        static::assertEquals($this->responseMock, $this->controller->execute());
    }


    /**
     * @param $exception
     * @param $expectedMessage
     * @dataProvider dataProviderTestExceptions
     */
    public function testExceptions($exception, $expectedMessage)
    {
        $this->requestMock->expects(static::any())->method('getParam')->willReturn([]);

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->prepareCartItems();

        $this->checkoutMock->expects(static::once())
            ->method('place')
            ->willThrowException($exception);

        $this->redirectMock->expects(static::once())
            ->method('redirect')
            ->with($this->responseMock, '*/*/review');
        
        $this->messagesManagerMock->expects(static::once())
            ->method('addErrorMessage')
            ->with($expectedMessage);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function dataProviderTestExceptions()
    {
        return [
            [
                'exception' => new \Magento\Framework\Exception\CouldNotSaveException(__('Unable to create order. Please try again later.')),
                'expectedMessage' => 'Unable to create order. Please try again later.'
            ],            [
                'exception' => new \Magento\Payment\Gateway\Command\CommandException(__('Something went wrong.')),
                'expectedMessage' => 'Something went wrong.'
            ],
            [
                'exception' => new \Exception('Something went wrong.'),
                'expectedMessage' => 'An error occurred while placing order. Please try again later.'
            ],
        ];
    }
}
