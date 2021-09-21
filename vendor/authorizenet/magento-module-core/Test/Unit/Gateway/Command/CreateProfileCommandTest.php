<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Command;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Gateway\Command\CreateProfileCommand;

class CreateProfileCommandTest extends TestCase
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
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $arrayResultFactoryMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultCodeValidatorMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Response\ProfileDetailsHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseHandlerMock;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transferObjectMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $validationResultMock;
    /**
     * @var CreateProfileCommand
     */
    protected $command;


    protected function setUp()
    {

        $this->transferFactoryMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Http\TransferFactory::class)->disableOriginalConstructor()->getMock();
        $this->transactionClientMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\ClientInterface::class)->getMockForAbstractClass();
        $this->requestBuilder = $this->getMockBuilder(\Magento\Payment\Gateway\Request\BuilderInterface::class)->getMockForAbstractClass();
        $this->arrayResultFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Command\Result\ArrayResultFactory::class)->getMockForAbstractClass();
        $this->resultCodeValidatorMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator::class)->disableOriginalConstructor()->getMock();
        $this->responseHandlerMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Response\ProfileDetailsHandler::class)->disableOriginalConstructor()->getMock();

        $this->transferObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Http\TransferInterface::class)->getMockForAbstractClass();
        $this->validationResultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterface::class)->getMockForAbstractClass();

        $this->command = new CreateProfileCommand(
            $this->transferFactoryMock,
            $this->transactionClientMock,
            $this->requestBuilder,
            $this->arrayResultFactoryMock,
            $this->resultCodeValidatorMock,
            $this->responseHandlerMock
        );
    }

    public function testExecute()
    {
        $subject = [];
        $request = ['request' => ['requestData']];
        $response = ['someResponse'];

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn($response);

        $this->resultCodeValidatorMock->expects(static::any())->method('validate')->with(['response' => $response])->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(true);

        $this->responseHandlerMock->expects(static::once())->method('handle')->with($subject, $response);

        $this->command->execute($subject);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Unable to create customer profile
     */
    public function testExecuteWithException()
    {
        $subject = [];
        $request = ['request' => ['requestData']];
        $response = ['someResponse'];

        $this->requestBuilder->expects(static::any())->method('build')->with($subject)->willReturn($request);
        $this->transferFactoryMock->expects(static::any())->method('create')->with($request)->willReturn($this->transferObjectMock);

        $this->transactionClientMock->expects(static::once())->method('placeRequest')->with($this->transferObjectMock)->willReturn($response);

        $this->resultCodeValidatorMock->expects(static::any())->method('validate')->with(['response' => $response])->willReturn($this->validationResultMock);

        $this->validationResultMock->expects(static::any())->method('isValid')->willReturn(false);

        $this->responseHandlerMock->expects(static::never())->method('handle')->with($subject, $response);

        $this->command->execute($subject);
    }
}
