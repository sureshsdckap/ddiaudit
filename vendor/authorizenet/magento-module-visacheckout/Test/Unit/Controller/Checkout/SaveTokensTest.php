<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Controller\Checkout;

use AuthorizeNet\VisaCheckout\Controller\Checkout\SaveTokens;

class SaveTokensTest extends AbstractControllerTest
{

    protected function setUp()
    {
        parent::setUp();

        $this->controller = new SaveTokens(
            $this->contextMock,
            $this->checkoutMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->formkeyValidatorMock
        );
    }

    public function testExecute()
    {

        $jsonResponseMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)->disableOriginalConstructor()->getMock();
        $this->resultFactoryMock->expects(static::once())->method('create')->willReturn($jsonResponseMock);
        
        $requestData = [
            'callId' => '124124124124',
            'encKey' => 'somekey',
            'encData' => 'somedata',
        ];

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);
        
        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['callId', null, $requestData['callId']],
                ['encKey', null, $requestData['encKey']],
                ['encData', null, $requestData['encData']],
            ]);
        
        $this->checkoutMock->expects(static::once())
            ->method('saveVcTokens')
            ->with($requestData['callId'], $requestData['encKey'], $requestData['encData']);
        
        $quoteId = '123';
        
        $this->quoteMock->expects(static::any())
            ->method('getId')
            ->willReturn($quoteId);
        
        $this->checkoutSessionMock->expects(static::once())
            ->method('setQuoteId')
            ->with($quoteId);
        
        $jsonResponseMock->expects(static::once())
            ->method('setData')
            ->with(['success' => true])
            ->willReturnSelf();

        static::assertEquals($jsonResponseMock, $this->controller->execute());
    }

    public function testExecuteInvalidFormkey()
    {

        $jsonResponseMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)->disableOriginalConstructor()->getMock();
        $this->resultFactoryMock->expects(static::once())->method('create')->willReturn($jsonResponseMock);

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(false);

        $exception = new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with($exception, __('Unable to process Visa Checkout tokens. Try again later.'));

        static::assertEquals($jsonResponseMock, $this->controller->execute());
    }

    /**
     * @param $exception
     * @param $expectedMessage
     * @dataProvider dataProviderTestExceptions
     */
    public function testExceptions($exception, $expectedMessage)
    {

        $jsonResponseMock = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)->disableOriginalConstructor()->getMock();
        $this->resultFactoryMock->expects(static::once())->method('create')->willReturn($jsonResponseMock);


        $requestData = [
            'callId' => '124124124124',
            'encKey' => 'somekey',
            'encData' => 'somedata',
        ];

        $this->requestMock->expects(static::any())
            ->method('getParam')
            ->willReturnMap([
                ['callId', null, $requestData['callId']],
                ['encKey', null, $requestData['encKey']],
                ['encData', null, $requestData['encData']],
            ]);

        $this->formkeyValidatorMock->expects(static::once())
            ->method('validate')
            ->with($this->requestMock)
            ->willReturn(true);

        $this->checkoutMock->expects(static::once())
            ->method('saveVcTokens')
            ->with($requestData['callId'], $requestData['encKey'], $requestData['encData'])
            ->willThrowException($exception);
        ;
        
        
        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with(static::isInstanceOf(get_class($exception)), $expectedMessage);

        $jsonResponseMock->expects(static::once())
            ->method('setHttpResponseCode')
            ->with(\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST)
            ->willReturnSelf();

        $jsonResponseMock->expects(static::once())
            ->method('setData')
            ->with(['message' => $expectedMessage])
            ->willReturnSelf();
        
        static::assertEquals($jsonResponseMock, $this->controller->execute());
    }
    
    public function dataProviderTestExceptions()
    {
        return [
            [
                'exception' => new \Magento\Payment\Gateway\Command\CommandException(__('Something went wrong.')),
                'expectedMessage' => 'Something went wrong.'
            ],
            [
                'exception' => new \Exception('Something went wrong.'),
                'expectedMessage' => 'Unable to process Visa Checkout tokens. Try again later.'
            ],
        ];
    }
}
