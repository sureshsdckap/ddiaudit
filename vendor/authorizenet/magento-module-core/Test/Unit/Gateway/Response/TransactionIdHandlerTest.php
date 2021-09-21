<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use net\authorize\api\contract\v1 as AnetAPI;

class TransactionIdHandlerTest extends TestCase
{

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    
    protected $subjectReaderMock;
    
    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var TransactionIdHandler
     */
    protected $handler;

    /**
     * @var AnetAPI\TransactionResponseType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionResponseType;


    public function setUp()
    {
        $this->initSubjectReaderMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
    }

    public function testHandle()
    {
        
        $transactionIdSuffix = '-capture';
        
        $this->handler = new TransactionIdHandler(
            $this->configMock,
            $this->subjectReaderMock,
            $transactionIdSuffix
        );
        
        $createTransactionResponse = $this
            ->getMockBuilder(AnetAPI\CreateTransactionResponse::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->subjectReaderMock->expects(static::once())
            ->method('readTransactionResponseObject')
            ->willReturn($createTransactionResponse);
        
        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);
        
        $this->transactionResponseType = $this->getMockBuilder(AnetAPI\TransactionResponseType::class)->disableOriginalConstructor()->getMock();
        
        $createTransactionResponse->expects(static::once())
            ->method('getTransactionResponse')
            ->willReturn($this->transactionResponseType);
        
        $refTransId = '121526171347';
        
        $this->transactionResponseType->expects(static::once())
            ->method('getRefTransId')
            ->willReturn($refTransId);
        
        $this->payment->expects(static::once())
            ->method('setParentTransactionId')
            ->with($refTransId)
            ->willReturnSelf();
        
        $transactionId = '13463477347';

        $this->transactionResponseType->expects(static::once())
            ->method('getTransId')
            ->willReturn($transactionId);

        $this->payment->expects(static::once())
            ->method('setTransactionId')
            ->with($transactionId . $transactionIdSuffix)
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('setLastTransId')
            ->with($transactionId . $transactionIdSuffix)
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('setAdditionalInformation')
            ->with('transactionId', $transactionId . $transactionIdSuffix)
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('setAdditionalInformation')
            ->with('transactionId', $transactionId . $transactionIdSuffix)
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('setIsTransactionClosed')
            ->with(false)
            ->willReturnSelf();

        $this->payment->expects(static::once())
            ->method('setShouldCloseParentTransaction')
            ->with(false)
            ->willReturnSelf();

        $this->handler->handle($subject, [$createTransactionResponse]);
    }

    /**
     * Create mock for subject reader
     */
    private function initSubjectReaderMock()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }
}
