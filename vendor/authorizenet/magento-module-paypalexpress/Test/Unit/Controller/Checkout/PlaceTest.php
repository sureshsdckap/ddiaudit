<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Controller\Checkout;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Controller\Checkout\Place;

class PlaceTest extends TestCase
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
     * @var \Magento\Framework\App\ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var Place
     */
    protected $controller;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|MockObject
     */
    protected $formkeyValidatorMock;

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface|MockObject
     */
    protected $agreementValidatorMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;


    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Model\Checkout::class)->disableOriginalConstructor()->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMockForAbstractClass();
        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();
        $this->formkeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)->disableOriginalConstructor()->getMock();
        $this->agreementValidatorMock = $this->getMockBuilder(\Magento\Checkout\Api\AgreementsValidatorInterface::class)->getMockForAbstractClass();

        $this->contextMock->expects(static::any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects(static::any())->method('getRedirect')->willReturn($this->redirectMock);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messageManagerMock);

        $this->controller = new Place(
            $this->contextMock,
            $this->checkoutMock,
            $this->formkeyValidatorMock,
            $this->agreementValidatorMock
        );
    }

    public function testExecute()
    {

        $this->formkeyValidatorMock->expects(static::once())->method('validate')->willReturn(true);
        $this->agreementValidatorMock->expects(static::once())->method('isValid')->willReturn(true);
        $this->requestMock->expects(static::any())->method('getParam')->with('agreement', [])->willReturn([]);

        $this->checkoutMock->expects(static::once())->method('place');
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, 'checkout/onepage/success', []);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    /**
     * @param $formkeyValidationResult
     * @param $agreementValidationResult
     * @param $expectedException
     * @param $expectedErrorMessage
     * @dataProvider dataProviderTestExecuteValidatorFails
     */
    public function testExecuteValidatorFails($formkeyValidationResult, $agreementValidationResult, $expectedException,  $expectedErrorMessage)
    {

        $this->formkeyValidatorMock->expects(static::any())->method('validate')->willReturn($formkeyValidationResult);
        $this->agreementValidatorMock->expects(static::any())->method('isValid')->willReturn($agreementValidationResult);
        $this->requestMock->expects(static::any())->method('getParam')->with('agreement', [])->willReturn([]);

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with($expectedException, $expectedErrorMessage);

        $this->checkoutMock->expects(static::never())->method('place');
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, '*/*/review', []);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function dataProviderTestExecuteValidatorFails()
    {
        return [
            [
                'formkeyValidationResult' => false,
                'agreementValidationResult' => true,
                'expectedException' => new \Magento\Framework\Exception\LocalizedException(__('Invalid form key')),
                'expectedErrorMessage' => null,
            ],
            [
                'formkeyValidationResult' => true,
                'agreementValidationResult' => false,
                'expectedException' => new \Magento\Framework\Exception\LocalizedException(__('Please accept checkout agreements')),
                'expectedErrorMessage' => null,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestExecuteWithException
     */
    public function testExecuteWithException($expectedException, $expectedErrorMessage)
    {

        $this->formkeyValidatorMock->expects(static::once())->method('validate')->willReturn(true);
        $this->agreementValidatorMock->expects(static::once())->method('isValid')->willReturn(true);
        $this->requestMock->expects(static::any())->method('getParam')->with('agreement', [])->willReturn([]);

        $this->checkoutMock->expects(static::once())->method('place')->willThrowException($expectedException);
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, '*/*/review', []);

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with($expectedException, $expectedErrorMessage);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function dataProviderTestExecuteWithException()
    {
        return [
            [
                'expectedException' => new \Magento\Framework\Exception\CouldNotSaveException(__('Some')),
                'expectedErrorMessage' => 'Unable to create order. Please try again later.',
            ],
            [
                'expectedException' => new \Magento\Framework\Exception\LocalizedException(__('Some')),
                'expectedErrorMessage' => null,
            ],
            [
                'expectedException' => new \Exception('Some'),
                'expectedErrorMessage' => 'An error occurred while placing order. Please try again later.',
            ],
        ];
    }
}
