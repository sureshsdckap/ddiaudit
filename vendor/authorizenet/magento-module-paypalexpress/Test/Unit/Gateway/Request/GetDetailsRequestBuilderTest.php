<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Request;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\PayPalExpress\Gateway\Request\GetDetailsRequestBuilder;

class GetDetailsRequestBuilderTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configReaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapter;

    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodInstance;

    /**
     * @var GetDetailsRequestBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();


        $this->builder = new GetDetailsRequestBuilder(
            $this->configReaderMock,
            $this->subjectReaderMock
        );
    }

    public function testBuild()
    {

        $merchantData = [
            'login_id' => '423123123',
            'transaction_key' => 'lnO#h23g0h23g',
            'solution_id' => '42424242',
        ];

        $subject = ['payment' => $this->getPaymentDataObjectMock(), 'initTransId' => '12312312321'];

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->willReturn($subject['payment']);
        $this->subjectReaderMock->expects(static::any())->method('readPayPalInitTransId')->willReturn($subject['initTransId']);

        $this->configReaderMock->expects(static::any())->method('getSolutionId')->with($this->methodInstance)->willReturn($merchantData['solution_id']);
        $this->configReaderMock->expects(static::atLeastOnce())->method('getLoginId')->willReturn($merchantData['login_id']);
        $this->configReaderMock->expects(static::atLeastOnce())->method('getTransactionKey')->willReturn($merchantData['transaction_key']);

        $request = $this->builder->build($subject);

        /* @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $request['request'];

        static::assertEquals($subject['initTransId'], $requestObject->getTransactionRequest()->getRefTransId(), 'Incorrect ref transaction id');
        static::assertEquals(\AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_GET_DETAILS, $requestObject->getTransactionRequest()->getTransactionType(), 'Incorrect transaction type');

        static::assertEquals($merchantData['solution_id'], $requestObject->getTransactionRequest()->getSolution()->getId(), 'Incorrect Solution id');
        static::assertEquals($merchantData['login_id'], $requestObject->getMerchantAuthentication()->getName(), 'Incorrect login id');
        static::assertEquals($merchantData['transaction_key'], $requestObject->getMerchantAuthentication()->getTransactionKey(), 'Incorrect transaction key');
    }

    private function getPaymentDataObjectMock()
    {
        $this->payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->methodInstance = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->methodInstance);

        $this->orderAdapter = $this->getMockBuilder(\Magento\Payment\Gateway\Data\Order\OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->payment);

        $mock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        return $mock;
    }
}
