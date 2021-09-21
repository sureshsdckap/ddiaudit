<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use net\authorize\api\contract\v1 as AnetAPI;

class CcInfoHandlerTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Response\CcInfoHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ccInfoHandler;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;
    /**
     * @var \Magento\Payment\Gateway\Data\Order\OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapter;

    protected function setUp()
    {
        $this->initSubjectReaderMock();

        $this->ccInfoHandler = new CcInfoHandler(
            $this->subjectReaderMock
        );
    }

    public function testHandle()
    {

        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);

        $transactionResponse = $this
            ->getMockBuilder(AnetAPI\TransactionResponseType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $accountNumber = '4111111111111111';

        $transactionResponse->expects(static::exactly(2))
            ->method('getAccountNumber')
            ->willReturn($accountNumber);

        $avsResponseCode = 'M';

        $transactionResponse->expects(static::exactly(2))
            ->method('getAvsResultCode')
            ->willReturn($avsResponseCode);

        $accountType = 'Visa';

        $transactionResponse->expects(static::once())
            ->method('getAccountType')
            ->willReturn($accountType);


        $authCode = 'APVNQ32';

        $transactionResponse->expects(static::once())
            ->method('getAuthCode')
            ->willReturn($authCode);

        $cvvCode = 'P';
        $cavvCode = '2';
        $transactionResponse->expects(static::once())->method('getCvvResultCode')->willReturn($cvvCode);
        $transactionResponse->expects(static::once())->method('getCavvResultCode')->willReturn($cavvCode);

        $createTransactionResponse = $this
            ->getMockBuilder(AnetAPI\CreateTransactionResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $createTransactionResponse->expects(static::once())
            ->method('getTransactionResponse')
            ->willReturn($transactionResponse);

        $this->subjectReaderMock->expects(static::once())
            ->method('readTransactionResponseObject')
            ->willReturn($createTransactionResponse);

        $this->payment->expects(static::once())
            ->method('setCcLast4')
            ->with(substr($accountNumber, -4, 4));

        $this->payment->expects(static::once())
            ->method('setCcAvsStatus')
            ->with($avsResponseCode);

        $this->payment->expects(static::exactly(6))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                ['cardType', $accountType],
                ['cardNumber', $accountNumber],
                ['avsResultCode', $avsResponseCode],
                ['authCode', $authCode],
                ['cvvResultCode', $cvvCode],
                ['cavvResultCode', $cavvCode]
            );

        $this->ccInfoHandler->handle($subject, [$createTransactionResponse]);
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

        $this->orderAdapter = $this->getMockBuilder(OrderAdapter::class)
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
