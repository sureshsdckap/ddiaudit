<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Response;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Gateway\Response\RawDataHandler;

class RawDataHandlerTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializerMock;

    /**
     * @var \AuthorizeNet\Core\Model\Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObject
     */
    protected $paymentDOMock;

    /**
     * @var RawDataHandler
     */
    protected $handler;

    protected function setUp()
    {

        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->serializerMock = $this->getMockBuilder(\Magento\Framework\Serialize\SerializerInterface::class)->getMockForAbstractClass();
        $this->loggerMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger::class)->disableOriginalConstructor()->getMock();

        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentDOMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)->disableOriginalConstructor()->getMock();
        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();

        $this->paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($this->methodMock);
        $this->paymentDOMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);


        $this->handler = new RawDataHandler(
            $this->subjectReaderMock,
            $this->serializerMock,
            $this->loggerMock
        );
    }

    public function testHandle()
    {

        $transId = '123123213';
        $subject = ['payment' => $this->paymentDOMock];
        $transactionInfo = [
            'some' => 'data',
            'someArray' => ['data', 'data']
        ];

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);
        $transactionResponseMock->expects(static::any())->method('getTransId')->willReturn($transId);

        $this->methodMock->expects(static::once())->method('fetchTransactionInfo')->with($this->paymentMock, $transId)->willReturn($transactionInfo);

        $this->paymentMock->expects(static::once())->method('setTransactionAdditionalInfo')->with(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $transactionInfo);

        $this->serializerMock->expects(static::atLeastOnce())->method('serialize')->willReturnArgument(0);

        $this->handler->handle($subject, [$responseMock]);
    }


    public function testHandleWithException()
    {

        $transId = '123123213';
        $subject = ['payment' => $this->paymentDOMock];
        $transactionInfo = [
            'some' => 'data',
            'someArray' => ['data', 'data']
        ];

        $exceptionMessage = __('something wrong');
        $exception = new \Magento\Framework\Exception\LocalizedException($exceptionMessage);

        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();
        $transactionResponseMock->expects(static::any())->method('getTransId')->willReturn($transId);

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock->expects(static::any())->method('getOrder')->willReturn($orderMock);

        $this->methodMock->expects(static::once())
            ->method('fetchTransactionInfo')
            ->with($this->paymentMock, $transId)->willThrowException($exception);
        $this->paymentMock->expects(static::never())
            ->method('setTransactionAdditionalInfo')
            ->with(\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, $transactionInfo);
        $this->loggerMock->expects(static::once())
            ->method('alert')
            ->with('Unable to get raw transaction data. Error was:' . $exceptionMessage);
        $orderMock->expects(static::once())
            ->method('addStatusHistoryComment')
            ->with(
                __('Please enable the transaction details API within the authorise.net portal to see additional '
                    . ' transaction details. See the Authorize.Net for magento 2 user manual for more information')
            );

        $this->handler->handle($subject, [$responseMock]);
    }
}
