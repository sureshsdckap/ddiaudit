<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Command;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Gateway\Command\GetMerchantDetailsCommand;

class GetMerchantDetailsCommandTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Http\TransferFactory|MockObject
     */
    protected $transferFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface|MockObject
     */
    protected $httpClientMock;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface|MockObject
     */
    protected $requestBuilderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator|MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferObjectMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationResultMock;

    /**
     * @var GetMerchantDetailsCommand
     */
    protected $command;

    protected function setUp()
    {
        $this->transferFactoryMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Http\TransferFactory::class)->disableOriginalConstructor()->getMock();
        $this->httpClientMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\ClientInterface::class)->disableOriginalConstructor()->getMock();
        $this->requestBuilderMock = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)->disableOriginalConstructor()->getMock();
        $this->validatorMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator::class)->disableOriginalConstructor()->getMock();

        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();
        $this->validationResultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)->getMockForAbstractClass();

        $this->command = new GetMerchantDetailsCommand(
            $this->transferFactoryMock,
            $this->httpClientMock,
            $this->requestBuilderMock,
            $this->validatorMock
        );
    }

    public function testExecute()
    {

        $commandSubject = [];

        $expectedDetails = [
            'isTestMode' => false,
            'clientKey' => 'asdasdqwfqwvqwvqvwv',
            'currencies' => ['USD'],
            'paymentMethods' => ['MasterCard'],
            'cardTypes' => ['MC']
        ];

        $request = ['request' => 'some request'];

        $response = $this->getMockBuilder(\net\authorize\api\contract\v1\GetMerchantDetailsResponse::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilderMock->expects(static::once())->method('build')->willReturn($request);

        $this->transferFactoryMock->expects(static::once())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->httpClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$response]);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(true);

        $this->validatorMock->expects(static::once())->method('validate')->with(['response' => [$response]])->willReturn($this->validationResultMock);
        $processor = $this->getMockBuilder(\net\authorize\api\contract\v1\ProcessorType::class)->disableOriginalConstructor()->getMock();

        $processor->expects(static::once())->method('getCardTypes')->willReturn($expectedDetails['cardTypes']);

        $response->method('getIsTestMode')->willReturn($expectedDetails['isTestMode']);
        $response->method('getPublicClientKey')->willReturn($expectedDetails['clientKey']);
        $response->method('getCurrencies')->willReturn($expectedDetails['currencies']);
        $response->method('getPaymentMethods')->willReturn($expectedDetails['paymentMethods']);
        $response->method('getProcessors')->willReturn([$processor]);

        static::assertEquals($expectedDetails, $this->command->execute($commandSubject));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Unable to get merchant details. Please verify your merchant Login Id and Transaction Key
     */
    public function testExecuteWithException()
    {

        $commandSubject = [];

        $expectedDetails = [
            'isTestMode' => false,
            'clientKey' => 'asdasdqwfqwvqwvqvwv',
            'currencies' => ['USD'],
            'paymentMethods' => ['MasterCard'],
            'cardTypes' => ['MC']
        ];

        $request = ['request' => 'some request'];

        $response = $this->getMockBuilder(\net\authorize\api\contract\v1\GetMerchantDetailsResponse::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilderMock->expects(static::once())->method('build')->willReturn($request);

        $this->transferFactoryMock->expects(static::once())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->httpClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$response]);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(false);

        $this->validatorMock->expects(static::once())->method('validate')->with(['response' => [$response]])->willReturn($this->validationResultMock);

        static::assertEquals($expectedDetails, $this->command->execute($commandSubject));
    }
}
