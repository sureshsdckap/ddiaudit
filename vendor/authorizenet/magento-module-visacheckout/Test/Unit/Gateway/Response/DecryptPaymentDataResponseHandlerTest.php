<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use net\authorize\api\contract\v1 as AnetAPI;

class DecryptPaymentDataResponseHandlerTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Response\CcInfoHandler|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $decryptHandler;

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

        $this->decryptHandler = new DecryptPaymentDataResponseHandler(
            $this->subjectReaderMock
            //            new SubjectReader()
        );
    }

    public function testHandle()
    {

        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);

        $decryptResponse = $this
            ->getMockBuilder(AnetAPI\DecryptPaymentDataResponse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readResponseObject')
            ->with(['response' => [$decryptResponse]])
            ->willReturn($decryptResponse);

        $billingInfo = 'asdasdas';
        $shipInfo = 'someShippingInfo';
        $paymentInfo = 'somePaymentInfo';
        $cardNumber = '4444';
        $ccInfo = $this->getMockBuilder(\net\authorize\api\contract\v1\CreditCardMaskedType::class)->getMock();

        $decryptResponse->expects(static::once())
            ->method('getBillingInfo')
            ->willReturn($billingInfo);

        $decryptResponse->expects(static::once())
            ->method('getShippingInfo')
            ->willReturn($shipInfo);

        $decryptResponse->expects(static::once())
            ->method('getPaymentDetails')
            ->willReturn($paymentInfo);
        
        $decryptResponse->expects(static::once())
            ->method('getCardInfo')
            ->willReturn($ccInfo);

        $ccInfo->expects(static::once())
            ->method('getCardNumber')
            ->willReturn('XXXXXXX' . $cardNumber);
        
        $this->payment->expects(static::once())
            ->method('setCcLast4')
            ->with($cardNumber);

        $this->payment->expects(static::exactly(1))
            ->method('setAdditionalInformation')
            ->with(
                DecryptPaymentDataResponseHandler::DATA_KEY_DECRYPTED_DATA,
                [
                    DecryptPaymentDataResponseHandler::DATA_KEY_BILLING_INFO => $billingInfo,
                    DecryptPaymentDataResponseHandler::DATA_KEY_SHIPPING_INFO => $shipInfo,
                    DecryptPaymentDataResponseHandler::DATA_KEY_PAYMENT_INFO => $paymentInfo,
                    DecryptPaymentDataResponseHandler::DATA_KEY_CARD_INFO => $ccInfo,
                ]
            );

        $this->decryptHandler->handle($subject, [$decryptResponse]);
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
