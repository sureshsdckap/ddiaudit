<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Request;

use AuthorizeNet\Core\Gateway\Request\GetTransactionDetailsRequestBuilder;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;


use PHPUnit\Framework\TestCase;

class GetTransactionDetailsRequestBuilderTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var GetTransactionDetailsRequestBuilder
     */
    protected $requestBuilder;
    
    protected $payment;


    /**
     * @var OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapterMock;
    
    protected $orderAdapter;

    protected function setUp()
    {

        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilder = new GetTransactionDetailsRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            false
        );
    }

    public function testBuild()
    {
        
        $subject['transactionId'] = '235235235';
        $subject['payment'] = $this->getPaymentDataObjectMock();

        $loginId = '1241552';
        $transKey = '76171552';

        $this->subjectReaderMock->expects(static::once())->method('readTransactionId')->willReturn($subject['transactionId']);
        $this->subjectReaderMock->expects(static::once())->method('readPayment')->willReturn($subject['payment']);
        
        $this->configMock->expects(static::atLeastOnce())->method('getLoginId')->willReturn($loginId);
        $this->configMock->expects(static::atLeastOnce())->method('getTransactionKey')->willReturn($transKey);

        $request = $this->requestBuilder->build($subject);
        /* @var \net\authorize\api\contract\v1\GetTransactionDetailsRequest $requestObject */
        $requestObject = $request['request'];

        static::assertEquals($subject['transactionId'], $requestObject->getTransId());
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->orderAdapterMock = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $mock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        return $mock;
    }
}
