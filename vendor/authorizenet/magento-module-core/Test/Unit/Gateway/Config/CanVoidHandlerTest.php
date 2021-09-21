<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class CanVoidHandlerTest extends TestCase
{

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;
    /**
     * @var CanVoidHandler
     */
    protected $handler;

    protected function setUp()
    {

        $this->initSubjectReaderMock();
        $this->handler = new CanVoidHandler();
    }

    public function testCanHandle()
    {

        $paymentDO = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentDO;

        $this->payment->expects(static::once())
            ->method('getAmountPaid')
            ->willReturn(0);

        self::assertEquals(true, $this->handler->handle($subject));
    }


    public function testCannotHandle()
    {

        $paymentDO = $this->getPaymentDataObjectMock();
        $subject['payment'] = $paymentDO;

        $this->payment->expects(static::once())
            ->method('getAmountPaid')
            ->willReturn(123);

        self::assertEquals(false, $this->handler->handle($subject));
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
