<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Response;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Gateway\Response\HeldHandler;

class HeldHandlerTest extends TestCase
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
     * @var HeldHandler
     */
    protected $handler;

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

        $this->serializerMock->expects(static::any())->method('unserialize')->willReturnCallback(
            function ($string) {
                $result = json_decode($string, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException('Unable to unserialize value.');
                }
                return $result;
            }
        );


        $this->handler = new HeldHandler(
            $this->subjectReaderMock,
            $this->serializerMock,
            $this->loggerMock
        );
    }

    public function testHandle()
    {

        $responseCode = 4;
        $transactionInfo = [
            HeldHandler::KEY_AFDS_ACTION => 'hold',
            HeldHandler::KEY_AFDS_FILTER_LIST => ['some', 'filter'],
        ];

        $subject = ['payment' => $this->paymentDOMock];
        $transId = '123123213';

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);

        $transactionResponseMock->expects(static::atLeastOnce())->method('getResponseCode')->willReturn($responseCode);
        $transactionResponseMock->expects(static::any())->method('getTransId')->willReturn($transId);

        $this->paymentMock->expects(static::once())->method('setIsTransactionPending')->with(true)->willReturnSelf();
        $this->paymentMock->expects(static::once())->method('setIsFraudDetected')->with(true)->willReturnSelf();

        $this->methodMock->expects(static::once())->method('fetchTransactionInfo')->with($this->paymentMock, $transId)->willReturn($transactionInfo);

        $this->paymentMock->expects(static::exactly(2))->method('setAdditionalInformation')->withConsecutive(
            [HeldHandler::KEY_AFDS_ACTION, $transactionInfo[HeldHandler::KEY_AFDS_ACTION]],
            [HeldHandler::KEY_AFDS_FILTER_LIST, $transactionInfo[HeldHandler::KEY_AFDS_FILTER_LIST]]
        );

        $this->handler->handle($subject, [$responseMock]);
    }

    public function testHandleFetchException()
    {

        $responseCode = 4;
        $transactionInfo = [
            HeldHandler::KEY_AFDS_ACTION => 'hold',
            HeldHandler::KEY_AFDS_FILTER_LIST => ['some', 'filter'],
        ];

        $subject = ['payment' => $this->paymentDOMock];
        $transId = '123123213';

        $exceptionMessage = 'Something went wrong';
        $exception = new \Magento\Framework\Exception\LocalizedException(__($exceptionMessage));

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);

        $transactionResponseMock->expects(static::atLeastOnce())->method('getResponseCode')->willReturn($responseCode);

        $this->paymentMock->expects(static::once())->method('setIsTransactionPending')->with(true)->willReturnSelf();
        $this->paymentMock->expects(static::once())->method('setIsFraudDetected')->with(true)->willReturnSelf();

        $transactionResponseMock->expects(static::any())->method('getTransId')->willReturn($transId);

        $this->methodMock->expects(static::once())->method('fetchTransactionInfo')->with($this->paymentMock, $transId)->willThrowException($exception);

        $this->loggerMock->expects(static::once())->method('alert')->with('Unable to fetch transaction details. Error was: ' . $exception->getMessage());

        $this->paymentMock->expects(static::never())->method('setAdditionalInformation')->withConsecutive(
            [HeldHandler::KEY_AFDS_ACTION, $transactionInfo[HeldHandler::KEY_AFDS_ACTION]],
            [HeldHandler::KEY_AFDS_FILTER_LIST, $transactionInfo[HeldHandler::KEY_AFDS_FILTER_LIST]]
        );

        $this->handler->handle($subject, [$responseMock]);
    }


    public function testHandleNonHeldCode()
    {

        $responseCode = 1;

        $subject = ['payment' => $this->paymentDOMock];

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);

        $transactionResponseMock->expects(static::atLeastOnce())->method('getResponseCode')->willReturn($responseCode);

        $this->paymentMock->expects(static::never())->method('setIsTransactionPending')->with(true)->willReturnSelf();
        $this->paymentMock->expects(static::never())->method('setIsFraudDetected')->with(true)->willReturnSelf();

        $this->handler->handle($subject, [$responseMock]);
    }

    public function testHandleNoPaymentModel()
    {

        $responseCode = 4;
        $subject = ['payment' => $this->paymentDOMock];

        $this->paymentMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)
            ->setMethods(['setIsTransactionPending', 'setIsFraudDetected'])
            ->getMockForAbstractClass();

        $this->paymentDOMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)->disableOriginalConstructor()->getMock();
        $this->paymentDOMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateTransactionResponse::class)->getMock();
        $transactionResponseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\TransactionResponseType::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->with($subject)->willReturn($this->paymentDOMock);
        $this->subjectReaderMock->expects(static::any())->method('readTransactionResponseObject')->with([$responseMock])->willReturn($responseMock);
        $responseMock->expects(static::any())->method('getTransactionResponse')->willReturn($transactionResponseMock);

        $transactionResponseMock->expects(static::atLeastOnce())->method('getResponseCode')->willReturn($responseCode);

        $this->paymentMock->expects(static::never())->method('setIsTransactionPending')->with(true)->willReturnSelf();
        $this->paymentMock->expects(static::never())->method('setIsFraudDetected')->with(true)->willReturnSelf();

        $this->handler->handle($subject, [$responseMock]);
    }
}
