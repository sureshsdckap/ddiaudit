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

class ClearDataHandlerTest extends TestCase
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
     * @var ClearDataHandler
     */
    protected $clearDataHandler;

    protected function setUp()
    {
        $this->initSubjectReaderMock();
        
        $this->clearDataHandler = new ClearDataHandler(
            $this->subjectReaderMock
        );
    }

    public function testClearData()
    {
        $keys = [
            'encKey',
            'encPaymentData',
            'opaque_data',
            'vault_cvv'
        ];
        
        $paymentDOMock = $this->getPaymentDataObjectMock();
        
        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($paymentDOMock);
        
        $this->payment
            ->method('hasAdditionalInformation')
            ->willReturn(true);

        $this->payment->expects(static::exactly(count($keys)))
            ->method('unsAdditionalInformation')
            ->withConsecutive(...array_map(
                function ($el) {
                    return [$el];
                },
                $keys
            ))
            ->willReturnSelf();
        
        $this->clearDataHandler->handle(['payment' => $paymentDOMock], []);
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
            ->setMethods(['getPayment', ])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        return $mock;
    }
}
