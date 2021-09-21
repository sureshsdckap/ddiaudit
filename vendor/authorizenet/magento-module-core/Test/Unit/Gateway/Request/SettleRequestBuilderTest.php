<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class SettleRequestBuilderTest extends TestCase
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
     * @var CcRefundRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapter;

    protected function setUp()
    {
        $this->initSubjectReaderMock();
        
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        
        $this->requestBuilder = new SettleRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_PRIOR_AUTH_CAPTURE
        );
    }


    public function testBuild()
    {
        
        $paymentDO = $this->getPaymentDataObjectMock();
        
        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($paymentDO);
        
        $solutionId = '12451552';
        
        $this->configMock->expects(static::atLeastOnce())
            ->method('getSolutionId')
            ->willReturn($solutionId);

        $loginId = '1241552';
        
        $this->configMock->expects(static::atLeastOnce())
            ->method('getLoginId')
            ->willReturn($loginId);

        $transKey = '76171552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getTransactionKey')
            ->willReturn($transKey);
        
        $orderIncrementId = '000000014';
        
        $this->orderAdapter->expects(static::atLeastOnce())
            ->method('getOrderIncrementId')
            ->willReturn($orderIncrementId);

        $this->orderAdapter->expects(static::atLeastOnce())->method('getCurrencyCode')->willReturn('EUR');

        $amount = 5.9901;
        
        $this->subjectReaderMock->expects(static::once())
            ->method('readAmount')
            ->willReturn($amount);
        
        $parentTransactionId = '12345678';
        
        $this->payment->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);
        
        $requestResult = $this->requestBuilder->build(['payment' => $this->payment, 'amount' => $amount]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];
        
        //assert transaction details
        static::assertEquals(
            \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_PRIOR_AUTH_CAPTURE,
            $requestObject->getTransactionRequest()->getTransactionType()
        );
        static::assertEquals(sprintf('%.2F', $amount), $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals($parentTransactionId, $requestObject->getTransactionRequest()->getRefTransId());
        static::assertEquals('EUR', $requestObject->getTransactionRequest()->getCurrencyCode());

        //make sure refId is not set
        static::assertEquals(null, $requestObject->getRefId());
        
        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());
        
        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }


    public function testBuildWithoutAmount()
    {

        $paymentDO = $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($paymentDO);

        $solutionId = '12451552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getSolutionId')
            ->willReturn($solutionId);

        $loginId = '1241552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getLoginId')
            ->willReturn($loginId);

        $transKey = '76171552';

        $this->configMock->expects(static::atLeastOnce())
            ->method('getTransactionKey')
            ->willReturn($transKey);

        $orderIncrementId = '000000014';

        $this->orderAdapter->expects(static::atLeastOnce())
            ->method('getOrderIncrementId')
            ->willReturn($orderIncrementId);

        $amount = false;

        $this->subjectReaderMock->expects(static::once())
            ->method('readAmount')
            ->willThrowException(new \InvalidArgumentException());

        $parentTransactionId = '12345678';

        $this->payment->expects(static::once())
            ->method('getParentTransactionId')
            ->willReturn($parentTransactionId);

        $requestResult = $this->requestBuilder->build(['payment' => $this->payment, 'amount' => $amount]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert transaction details
        static::assertEquals(
            \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_PRIOR_AUTH_CAPTURE,
            $requestObject->getTransactionRequest()->getTransactionType()
        );
        static::assertEquals(null, $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals($parentTransactionId, $requestObject->getTransactionRequest()->getRefTransId());

        //make sure refId is not set
        static::assertEquals(null, $requestObject->getRefId());

        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
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

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->orderAdapter = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

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
