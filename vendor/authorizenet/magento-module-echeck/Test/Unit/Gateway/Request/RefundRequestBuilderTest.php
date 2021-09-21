<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Test\Unit\Gateway\Request;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\ECheck\Gateway\Request\RefundRequestBuilder;
use AuthorizeNet\Core\Gateway\Request\OpaqueDataTransactionRequestBuilder;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;
use Magento\Sales\Model\Order;

class RefundRequestBuilderTest extends TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var OpaqueDataTransactionRequestBuilder
     */
    private $requestBuilder;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|MockObject
     */
    private $configMock;

    /**
     * @var Payment|MockObject
     */
    private $payment;

    /**
     * @var OrderAdapter|MockObject
     */
    private $orderAdapter;

    /**
     * @var Order|MockObject
     */
    private $order;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var AddressAdapterInterface|MockObject
     */
    private $customerAddress;

    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->transactionType = 'authCaptureTransaction';

        $this->requestBuilder = new RefundRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            $this->transactionType
        );
    }

    public function testBuild()
    {
        $paymentDO = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($paymentDO);

        $amount = 5.99;

        $this->subjectReaderMock->expects(static::once())
            ->method('readAmount')
            ->willReturn($amount);

        $parentTransactionId = 'parentTran51D';

        $this->payment->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);

        $routingNumber = '10001100010';
        $accountNumber = '01001110010';
        $accountName = 'Name';
        $accountType = 'checking';
        $echeckType = 'PPD';

        $this->subjectReaderMock->expects(static::once())
            ->method('readECheckRoutingNumber')
            ->willReturn($routingNumber);

        $this->subjectReaderMock->expects(static::once())
            ->method('readECheckAccountNumber')
            ->willReturn($accountNumber);

        $this->subjectReaderMock->expects(static::once())
            ->method('readECheckNameOnAccount')
            ->willReturn($accountName);

        $this->subjectReaderMock->expects(static::once())
            ->method('readECheckAccountType')
            ->willReturn($accountType);

        $solutionId = '12451552';

        $this->configMock->expects(static::once())
            ->method('getSolutionId')
            ->willReturn($solutionId);

        $invoceNumber = '100001';

        $this->orderAdapter->expects(static::once())
            ->method('getOrderIncrementId')
            ->willReturn($invoceNumber);

        $this->orderAdapter->expects(static::atLeastOnce())->method('getCurrencyCode')->willReturn('EUR');

        $loginId = 'log1n1d';

        $this->configMock->expects(static::once())
            ->method('getLoginId')
            ->willReturn($loginId);

        $transKey = 'tran5Key';

        $this->configMock->expects(static::once())
            ->method('getTransactionKey')
            ->willReturn($transKey);

        $requestResult = $this->requestBuilder->build(['payment' => $this->payment, 'amount' => $amount]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert transaction details
        static::assertEquals($this->transactionType, $requestObject->getTransactionRequest()->getTransactionType());
        static::assertEquals(sprintf('%.2F', $amount), $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals($parentTransactionId, $requestObject->getTransactionRequest()->getRefTransId());
        static::assertEquals('EUR', $requestObject->getTransactionRequest()->getCurrencyCode());

        //payment details
        $transactionRequestPayment = $requestObject->getTransactionRequest()->getPayment();
        static::assertEquals(RefundRequestBuilder::ECHECK_MASK . $routingNumber, $transactionRequestPayment->getBankAccount()->getRoutingNumber());
        static::assertEquals(RefundRequestBuilder::ECHECK_MASK . $accountNumber, $transactionRequestPayment->getBankAccount()->getAccountNumber());
        static::assertEquals($accountName, $transactionRequestPayment->getBankAccount()->getNameOnAccount());
        static::assertEquals($accountType, $transactionRequestPayment->getBankAccount()->getAccountType());
        static::assertEquals($echeckType, $transactionRequestPayment->getBankAccount()->getEcheckType());

        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    private function getPaymentDataObjectMock()
    {
        $this->orderAdapter = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->setMethods(['getAdditionalInformation', 'getParentTransactionId', 'getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $mock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        return $mock;
    }
}
