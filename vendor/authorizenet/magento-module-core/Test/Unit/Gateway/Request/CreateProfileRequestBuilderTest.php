<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Request;

use AuthorizeNet\Core\Gateway\Request\CreateProfileRequestBuilder;
use PHPUnit\Framework\TestCase;

class CreateProfileRequestBuilderTest extends TestCase
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
     * @var CreateProfileRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapterMock;

    protected function setUp()
    {

        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilder = new CreateProfileRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            false
        );
    }

    public function testBuild()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        
        $this->subjectReaderMock->method('readPayment')->willReturn($subject['payment']);

        $customerId = '423123';
        
        $this->orderAdapterMock->expects(static::any())->method('getCustomerId')->willReturn($customerId);
        
        $transId = '1231231232';
        
        $this->paymentMock->expects(static::once())->method('getTransactionId')->willReturn($transId);

        $loginId = '1241552';

        $this->configMock->expects(static::atLeastOnce())->method('getLoginId')->willReturn($loginId);

        $transKey = '76171552';

        $this->configMock->expects(static::atLeastOnce())->method('getTransactionKey')->willReturn($transKey);

        $requestResult = $this->requestBuilder->build($subject);
        
        /** @var \net\authorize\api\contract\v1\CreateCustomerProfileFromTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];
        
        static::assertEquals($customerId, $requestObject->getCustomer()->getMerchantCustomerId());
        static::assertEquals($transId, $requestObject->getTransId());
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    private function getPaymentDataObjectMock()
    {
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->orderAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\Order\OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $mock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapterMock);

        return $mock;
    }
}
