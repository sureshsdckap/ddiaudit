<?php
/**
 *
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Controller\Checkout;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class SaveTokenTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Action\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory|MockObject
     */
    protected $resultJsonFactoryMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout|MockObject
     */
    protected $checkoutMock;

    /**
     * @var \Magento\Framework\Controller\Result\Json|MockObject
     */
    protected $jsonResultMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Controller\Checkout\SaveToken
     */
    protected $saveTokenController;

    /**
     * @var \Magento\Framework\App\RequestInterface|MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|MockObject
     */
    protected $formkeyValidatorMock;

    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->resultJsonFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\JsonFactory::class)->disableOriginalConstructor()->getMock();
        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Model\Checkout::class)->disableOriginalConstructor()->getMock();

        $this->formkeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)->disableOriginalConstructor()->getMock();

        $this->jsonResultMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $this->resultJsonFactoryMock->expects(static::atLeastOnce())->method('create')->willReturn($this->jsonResultMock);

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);

        $this->saveTokenController = new \AuthorizeNet\PayPalExpress\Controller\Checkout\SaveToken(
            $this->contextMock,
            $this->resultJsonFactoryMock,
            $this->checkoutMock,
            $this->formkeyValidatorMock
        );
    }

    public function testExecute()
    {

        $token = 'asdasd';
        $initTransId = '123123123123';

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap(
            [
                ['token', null, $token],
                ['transId', null, $initTransId],
            ]
        );

        $this->formkeyValidatorMock->expects(static::once())->method('validate')->with($this->requestMock)->willReturn(true);

        $this->checkoutMock->expects(static::once())->method('saveTokenData')->with(
            ['token' => $token, 'transId' => $initTransId]
        );

        $this->jsonResultMock->expects(static::once())->method('setData')->with(['status' => true]);

        static::assertEquals($this->jsonResultMock, $this->saveTokenController->execute());
    }


    public function testExecuteValidatorFails()
    {

        $this->formkeyValidatorMock->expects(static::any())->method('validate')->with($this->requestMock)->willReturn(false);

        $this->checkoutMock->expects(static::never())->method('updateShippingMethod');

        $this->jsonResultMock->expects(static::once())->method('setData')->with(['status' => false, 'error' => 'Invalid form key']);

        static::assertEquals($this->jsonResultMock, $this->saveTokenController->execute());

    }
    /**
     * @dataProvider dataProviderTestExecuteNegative
     */
    public function testExecuteNegative($exceptionClass, $exceptionMessage, $expectedMessage)
    {

        $token = 'asdasd';
        $initTransId = '123123123123';
        $exception = new $exceptionClass($exceptionMessage);

        $this->formkeyValidatorMock->expects(static::any())->method('validate')->with($this->requestMock)->willReturn(true);

        $this->requestMock->expects(static::any())->method('getParam')->willReturnMap(
            [
                ['token', null, $token],
                ['transId', null, $initTransId],
            ]
        );

        $this->checkoutMock
            ->expects(static::once())
            ->method('saveTokenData')
            ->with(['token' => $token, 'transId' => $initTransId])
            ->willThrowException($exception);

        $this->jsonResultMock->expects(static::once())->method('setData')->with(['status' => false, 'error' => $expectedMessage]);

        static::assertEquals($this->jsonResultMock, $this->saveTokenController->execute());
    }

    public function dataProviderTestExecuteNegative()
    {
        return [
            [
                'exceptionClass' => \Exception::class,
                'exceptionMessage' => __('We are unable to initialize Paypal Express Checkout.'),
                'expectedMessage' => 'We are unable to initialize Paypal Express Checkout.',
            ],            [
                'exceptionClass' => \Magento\Framework\Exception\LocalizedException::class,
                'exceptionMessage' => __('Something went wrong'),
                'expectedMessage' => 'Something went wrong',
            ],
        ];
    }
}
