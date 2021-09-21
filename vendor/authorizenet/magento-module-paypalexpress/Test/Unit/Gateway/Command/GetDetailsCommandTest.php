<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Command;

use AuthorizeNet\PayPalExpress\Gateway\Command\GetDetailsCommand;
use PHPUnit\Framework\TestCase;

class GetDetailsCommandTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Http\TransferFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Http\ClientInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionClientMock;

    /**
     * @var \Magento\Payment\Gateway\Request\BuilderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestBuilderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultCodeValidatorMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferObjectMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationResultMock;


    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestSerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerMock;

    /**
     * @var \net\authorize\api\contract\v1\CreateTransactionResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseObjectMock;

    /**
     * @var \net\authorize\api\contract\v1\TransactionResponseType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResponseMock;

    /**
     * @var GetDetailsCommand
     */
    protected $command;

    protected function setUp()
    {

        $this->transferFactoryMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Http\TransferFactory::class)->disableOriginalConstructor()->getMock();
        $this->transactionClientMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\ClientInterface::class)->getMockForAbstractClass();
        $this->requestBuilderMock = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)->getMockForAbstractClass();
        $this->resultCodeValidatorMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator::class)->disableOriginalConstructor()->getMock();

        $this->serializerMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestSerializerInterface::class)->disableOriginalConstructor()->getMock();
        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();
        $this->validationResultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)->getMockForAbstractClass();
        $this->responseObjectMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->disableOriginalConstructor()->getMock();
        $this->transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->disableOriginalConstructor()->getMock();

        $this->command = new GetDetailsCommand(
            $this->transferFactoryMock,
            $this->requestBuilderMock,
            $this->transactionClientMock,
            $this->resultCodeValidatorMock
        );
    }

    public function testExecute()
    {
        $subject = [];
        $request = ['request' => 'somerequest'];

        $this->requestBuilderMock->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);
        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$this->responseObjectMock]);
        $this->resultCodeValidatorMock->expects(static::once())->method('validate')->with(['response' => [$this->responseObjectMock]])->willReturn($this->validationResultMock);
        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(true);

        $this->responseObjectMock->expects(static::any())->method('getTransactionResponse')->willReturn($this->transactionResponseMock);

        static::assertEquals($this->transactionResponseMock, $this->command->execute($subject));
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage Unable to get transaction details
     */
    public function testExecuteWithException()
    {
        $subject = [];
        $request = ['request' => 'somerequest'];

        $this->requestBuilderMock->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);
        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$this->responseObjectMock]);
        $this->resultCodeValidatorMock->expects(static::once())->method('validate')->with(['response' => [$this->responseObjectMock]])->willReturn($this->validationResultMock);
        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(false);

        static::assertEquals($this->transactionResponseMock, $this->command->execute($subject));
    }
}
