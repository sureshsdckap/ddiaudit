<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Helper;

use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use net\authorize\api\contract\v1 as AnetAPI;

class SubjectReaderTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var AnetAPI\ANetApiResponseType
     */
    protected $anetResponseType;

    /**
     * @var AnetAPI\CreateTransactionResponse
     */
    protected $anetTransactionResponseType;

    /**
     * @var AnetAPI\CreateCustomerProfileResponse
     */
    protected $anetCreateCustomerProfileResponseType;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
        
        $this->anetResponseType = new AnetAPI\ANetApiResponseType();
        $this->anetTransactionResponseType = new AnetAPI\CreateTransactionResponse();
        $this->anetCreateCustomerProfileResponseType = new AnetAPI\CreateCustomerProfileResponse();
    }

    public function testReadPayment()
    {
//        $subject['payment'] = $this->getPaymentDataObjectMock();
//            
//        $this->payment-            
    }

    public function testReadAmount()
    {
        $amount = '99.95';
        static::assertEquals($amount, $this->subjectReader->readAmount(['amount' => $amount]));
    }

    public function testReadOpaqueData()
    {

        $subject['payment'] = $this->getPaymentDataObjectMock();
        $opaqueData = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['opaque_data' => $opaqueData]);
        
        static::assertEquals($opaqueData, $this->subjectReader->readOpaqueData($subject));
    }


    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Opaque data does not exist
     */
    public function testReadOpaqueDataWithException()
    {

        $subject['payment'] = $this->getPaymentDataObjectMock();
        $opaqueData = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn([]);

        static::assertEquals($opaqueData, $this->subjectReader->readOpaqueData($subject));
    }

    public function testReadECheckRoutingNumber()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = '100100101001';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['routingNumber' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckRoutingNumber($subject));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ECheck routing number does not exist.
     */
    public function testReadECheckRoutingNumberWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = null;

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['routingNumber' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckRoutingNumber($subject));
    }


    public function testReadECheckAccountNumber()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = '100100101001';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountNumber' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckAccountNumber($subject));
    }
    
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ECheck account number does not exist.
     */
    public function testReadECheckAccountNumberWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = false;

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountNumber' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckAccountNumber($subject));
    }
    
    public function testReadECheckAccountType()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = 'checking';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountType' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckAccountType($subject));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ECheck account type does not exist.
     */
    public function testReadECheckAccountTypeWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = false;

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountType' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckAccountType($subject));
    }

    public function testReadECheckNameOnAccount()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = 'checking';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountName' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckNameOnAccount($subject));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage ECheck account name does not exist.
     */
    public function testReadECheckNameOnAccountWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $data = null;

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['accountName' => $data]);

        static::assertEquals($data, $this->subjectReader->readECheckNameOnAccount($subject));
    }
    
    public function testReadPayPalInitTransId()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $initTransId = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['initTransId' => $initTransId]);

        static::assertEquals($initTransId, $this->subjectReader->readPayPalInitTransId($subject));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage PayPal Initial Transaction Id is not provided.
     */
    public function testReadPayPalInitTransIdWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $initTransId = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn([]);

        static::assertEquals($initTransId, $this->subjectReader->readPayPalInitTransId($subject));
    }

    public function testReadPayPalPayerId()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $payerId = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['payerId' => $payerId]);

        static::assertEquals($payerId, $this->subjectReader->readPayPalPayerId($subject));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage PayPal PayerId does not exist.
     */
    public function testReadPayPalPayerIdWithException()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $payerId = 'asdasdasd';

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn([]);

        static::assertEquals($payerId, $this->subjectReader->readPayPalPayerId($subject));
    }

    public function testReadIsTokenEnabled()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['is_active_payment_token_enabler' => true]);

        static::assertEquals(true, $this->subjectReader->readIsTokenEnabled($subject));
    }

    public function testReadIsTokenEnabledNegative()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn([]);

        static::assertEquals(false, $this->subjectReader->readIsTokenEnabled($subject));
    }

    public function testReadPublicHash()
    {
        $publicHash = 'asdf';
        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn(['public_hash' => $publicHash]);

        static::assertEquals($publicHash, $this->subjectReader->readPublicHash($subject));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Public Hash should be provided
     */
    public function testReadPublicHashWithException()
    {
        $publicHash = 'asdf';
        $subject['payment'] = $this->getPaymentDataObjectMock();

        $this->payment->expects(static::once())
            ->method('getAdditionalInformation')
            ->willReturn([]);

        static::assertEquals($publicHash, $this->subjectReader->readPublicHash($subject));
    }

    public function testReadResponseObject()
    {
        static::assertEquals(
            $this->anetResponseType,
            $this->subjectReader->readResponseObject(['response' => [$this->anetResponseType]])
        );
    }

    /**
     * @covers \AuthorizeNet\Core\Gateway\Helper\SubjectReader::readResponseObject
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response data object should be provided
     */
    public function testReadResponseObjectWithException()
    {
        static::assertEquals(
            $this->anetResponseType,
            $this->subjectReader->readResponseObject(['response' => []])
        );
    }

    public function testReadTransactionResponseObject()
    {
        static::assertEquals(
            $this->anetTransactionResponseType,
            $this->subjectReader->readTransactionResponseObject([$this->anetTransactionResponseType])
        );
    }

    /**
     * @covers \AuthorizeNet\Core\Gateway\Helper\SubjectReader::readTransactionResponseObject
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response data object type is invalid
     */
    public function testReadTransactionResponseObjectWithException()
    {
        static::assertEquals(
            $this->anetTransactionResponseType,
            $this->subjectReader->readTransactionResponseObject([$this->anetResponseType])
        );
    }

    public function testReadCreateCustomerProfileResponseObject()
    {
        static::assertEquals(
            $this->anetCreateCustomerProfileResponseType,
            $this->subjectReader->readCreateCustomerProfileResponseObject([$this->anetCreateCustomerProfileResponseType])
        );
    }

    /**
     * @covers \AuthorizeNet\Core\Gateway\Helper\SubjectReader::readCreateCustomerProfileResponseObject()
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Response data object type is invalid
     */
    public function testReadCreateCustomerProfileResponseObjectWithException()
    {
        static::assertEquals(
            $this->anetCreateCustomerProfileResponseType,
            $this->subjectReader->readCreateCustomerProfileResponseObject([$this->anetResponseType])
        );
    }

    public function testReadTransactionId()
    {
        
        $subject['transactionId'] = '123123123123';
        
        static::assertEquals($subject['transactionId'], $this->subjectReader->readTransactionId($subject));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Transaction id does not exist
     */
    public function testReadTransactionIdWithException()
    {

        $subject['transactionId'] = false;

        static::assertEquals($subject['transactionId'], $this->subjectReader->readTransactionId($subject));
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
