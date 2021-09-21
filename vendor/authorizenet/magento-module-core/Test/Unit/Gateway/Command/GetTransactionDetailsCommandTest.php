<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Command;

use AuthorizeNet\Core\Gateway\Command\GetTransactionDetailsCommand;
use PHPUnit\Framework\TestCase;

class GetTransactionDetailsCommandTest extends TestCase
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
    protected $requestBuilder;

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
     * @var \net\authorize\api\contract\v1\GetTransactionDetailsResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseObjectMock;

    /**
     * @var GetTransactionDetailsCommand
     */
    protected $command;


    protected function setUp()
    {

        $this->transferFactoryMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Http\TransferFactory::class)->disableOriginalConstructor()->getMock();
        $this->transactionClientMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\ClientInterface::class)->getMockForAbstractClass();
        $this->requestBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)->getMockForAbstractClass();
        $this->resultCodeValidatorMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator::class)->disableOriginalConstructor()->getMock();

        $this->serializerMock = $this->getMockBuilder(\AuthorizeNet\Core\Service\AnetRequestSerializerInterface::class)->disableOriginalConstructor()->getMock();
        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();
        $this->validationResultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)->getMockForAbstractClass();
        $this->responseObjectMock = $this->getMockBuilder(\net\authorize\api\contract\v1\GetTransactionDetailsResponse::class)->disableOriginalConstructor()->getMock();

        $this->command = new GetTransactionDetailsCommand(
            $this->transferFactoryMock,
            $this->requestBuilder,
            $this->transactionClientMock,
            $this->resultCodeValidatorMock,
            $this->serializerMock
        );
    }

    public function testExecute()
    {
        $subject = [];
        $request = ['request' => ['requestData']];
        $transactionDetails = ['someTransactionData'];

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);
        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$this->responseObjectMock]);
        $this->resultCodeValidatorMock->expects(static::any())->method('validate')->with(['response' => [$this->responseObjectMock]])->willReturn($this->validationResultMock);
        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(true);
        $this->serializerMock->expects(static::once())->method('toArray')->with($this->responseObjectMock->getTransaction())->willReturn($transactionDetails);

        static::assertEquals($transactionDetails, $this->command->execute($subject));
    }

    public function testExecuteWithReturnObject()
    {
        $subject = ['resultAsObject' => true];
        $request = ['request' => ['requestData']];
        $transactionDetails = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionDetailsType::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$this->responseObjectMock]);

        $this->resultCodeValidatorMock->expects(static::any())->method('validate')->with(['response' => [$this->responseObjectMock]])->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(true);

        $this->responseObjectMock->expects(static::once())->method('getTransaction')->willReturn($transactionDetails);

        static::assertEquals($transactionDetails, $this->command->execute($subject));
    }

    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage Unable to get transaction details
     */
    public function testExecuteWithValidationException()
    {
        $subject = [];
        $request = ['request' => ['requestData']];
        $transactionDetails = ['someTransactionData'];

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn([$this->responseObjectMock]);

        $this->resultCodeValidatorMock->expects(static::any())->method('validate')->with(['response' => [$this->responseObjectMock]])->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(false);

        $this->command->execute($subject);
    }


    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     * @expectedExceptionMessage Unable to get transaction details
     */
    public function testExecuteWithRequestException()
    {
        $subject = [];
        $request = ['request' => ['requestData']];

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $exception = new \Magento\Framework\Exception\LocalizedException(__('Something went wrong while doing request'));
        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willThrowException($exception);

        $this->command->execute($subject);
    }
}
