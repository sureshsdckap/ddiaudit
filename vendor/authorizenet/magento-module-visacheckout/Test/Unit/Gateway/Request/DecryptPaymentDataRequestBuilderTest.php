<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Gateway\Request;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use PHPUnit\Framework\TestCase;

class DecryptPaymentDataRequestBuilderTest extends TestCase
{

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var DecryptPaymentDataRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    protected function setUp()
    {
        $this->initSubjectReaderMock();

        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilder = new DecryptPaymentDataRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock
        );
    }


    public function testBuild()
    {

        $subject = ['payment' => $this->getPaymentDataObjectMock()];

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);

        $loginId = '1241552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getLoginId')
            ->willReturn($loginId);

        $transKey = '76171552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getTransactionKey')
            ->willReturn($transKey);
        
        
        $encKey = '2o4nin34i]vn1i34]ii13j]ivo3';
        $encData = 'pnvionqio3n5ibvq3o5nbono3qi5nb3bm3b';
        $callId = 'q243ivpq3nvp3qvp3v';

        $this->payment->expects(static::any())
            ->method('getAdditionalInformation')
            ->willReturnMap(
                [
                    ['encKey', $encKey],
                    ['encPaymentData', $encData],
                    ['callId', $callId],
                ]
            );
        
        $requestResult = $this->requestBuilder->build($subject);

        /** @var \net\authorize\api\contract\v1\DecryptPaymentDataRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
        
        static::assertEquals($callId, $requestObject->getCallId());
        static::assertEquals($encKey, $requestObject->getOpaqueData()->getDataKey());
        static::assertEquals($encData, $requestObject->getOpaqueData()->getDataValue());
        static::assertEquals(\AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::VC_DATA_DESCRIPTOR, $requestObject->getOpaqueData()->getDataDescriptor());
    }


    private function initSubjectReaderMock()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)
            ->disableOriginalConstructor()
            ->getMock();
    }


    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

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
