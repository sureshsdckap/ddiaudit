<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use AuthorizeNet\Core\Gateway\Response\TransactionIdHandler;
use PHPUnit\Framework\TestCase;
use \AuthorizeNet\Core\Gateway\Request\UpdateHeldTransactionRequest;

class UpdateHeldTransactionRequestTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configReaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapterMock;

    protected $actionType;

    /**
     * @var UpdateHeldTransactionRequest
     */
    protected $builder;


    protected function setUp()
    {
        $this->configReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->actionType = \AuthorizeNet\Core\Gateway\Request\UpdateHeldTransactionRequest::ACTION_DECLINE;

        $this->builder = new UpdateHeldTransactionRequest(
            $this->configReaderMock,
            $this->subjectReaderMock,
            '',
            $this->actionType
        );
    }

    public function testBuild()
    {
        $subject = ['payment' => $this->getPaymentDataObjectMock()];

        $merchantData = [
            'login_id' => '423123123',
            'transaction_key' => 'lnO#h23g0h23g',
        ];

        $transactionId = '121254125215';

        $this->subjectReaderMock->expects(static::any())->method('readPayment')->willReturn($subject['payment']);
        $this->configReaderMock->expects(static::atLeastOnce())->method('getLoginId')->willReturn($merchantData['login_id']);
        $this->configReaderMock->expects(static::atLeastOnce())->method('getTransactionKey')->willReturn($merchantData['transaction_key']);

        $this->paymentMock->expects(static::any())
            ->method('getAdditionalInformation')
            ->with(TransactionIdHandler::TRANSACTION_ID)
            ->willReturn($transactionId);

        $request = $this->builder->build($subject);

        /* @var \net\authorize\api\contract\v1\UpdateHeldTransactionRequest $requestObject */
        $requestObject = $request['request'];

        static::assertEquals($transactionId, $requestObject->getHeldTransactionRequest()->getRefTransId(), 'ref transaction id invalid');
        static::assertEquals($this->actionType, $requestObject->getHeldTransactionRequest()->getAction(), 'action is invalid');
        static::assertEquals($merchantData['login_id'], $requestObject->getMerchantAuthentication()->getName(), 'login id invalid');
        static::assertEquals($merchantData['transaction_key'], $requestObject->getMerchantAuthentication()->getTransactionKey(), 'transaction key invalid');

        $this->builder->build($subject);
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

        $mock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);

        $mock->expects(static::any())->method('getOrder')->willReturn($this->orderAdapterMock);

        return $mock;
    }
}
