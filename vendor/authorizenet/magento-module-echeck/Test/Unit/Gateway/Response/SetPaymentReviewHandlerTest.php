<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Test\Unit\Gateway\Response;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\ECheck\Gateway\Response\SetPaymentReviewHandler;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class SetPaymentReviewHandlerTest extends TestCase
{
    /**
     * @var Payment|MockObject
     */
    protected $payment;

    /**
     * @var SetPaymentReviewHandler|MockObject
     */
    private $setPaymentReviewHandler;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    public function setUp()
    {
        $this->initSubjectReaderMock();
        $this->setPaymentReviewHandler = new SetPaymentReviewHandler(
            $this->subjectReaderMock
        );
    }

    public function testHandle()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);

        $this->payment->expects(static::once())
            ->method('setIsTransactionPending')
            ->with(true)
            ->willReturnSelf();

        $this->setPaymentReviewHandler->handle($subject, []);
    }

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
