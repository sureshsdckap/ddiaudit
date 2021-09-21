<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use AuthorizeNet\Core\Test\Unit\Gateway\Request\OpaqueDataTransactionRequestBuilderTestHelper;
use Magento\Payment\Gateway\Data\AddressAdapterInterface;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

/**
 * Class TransactionRequestBuilderTest
 * @package AuthorizeNet\CreditCard\Test\Unit\Gateway\Request
 */
class OpaqueDataTransactionRequestBuilderTest extends TestCase
{
    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var OpaqueDataTransactionRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * @var OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapter;

    /**
     * @var Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $order;

    protected $transactionType;

    protected $invoceNumber;

    protected $customerAddress;

    protected $buildData;

    protected $paymentDo;

    /**
     * @var \AuthorizeNet\Core\Gateway\Request\OpaqueDataTransactionRequestBuilderTestHelper
     */
    protected $requestBuilderHelper;

    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->customerAddress = $this->getMockBuilder(AddressAdapterInterface::class)->disableOriginalConstructor()->getMock();
        $this->transactionType = 'authCaptureTransaction';


        $this->requestBuilder = new OpaqueDataTransactionRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            $this->transactionType
        );

        $this->requestBuilderHelper = new OpaqueDataTransactionRequestBuilderTestHelper(
            $this->configMock,
            $this->subjectReaderMock,
            $this->transactionType
        );
    }

    /**
     * @param $data
     * @param $expected
     */
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

        $invoceNumber = '100001';

        $this->orderAdapter->expects(static::exactly(2))
            ->method('getOrderIncrementId')
            ->willReturn($invoceNumber);

        $opaqueData = [
            'dataDescriptor'=> 'somevalue',
            'dataValue' => 'someanothervalue',
        ];

        $this->subjectReaderMock->expects(static::once())
            ->method('readOpaqueData')
            ->willReturn(json_encode($opaqueData));

        $addressValues = [
            'John',
            'Smith',
            'US',
            'NewYork',
            'NY',
            'Street 1',
            'Street 2',
            '10001',
            'Visa',
            '88008000800',
            'customer@mail.com'
        ];
        $addressMethods = [
            'getFirstname',
            'getLastname',
            'getCountryId',
            'getCity',
            'getRegionCode',
            'getStreetLine1',
            'getStreetLine2',
            'getPostcode',
            'getCompany',
            'getTelephone',
            'getEmail'
        ];
        $customerAddressMethods = [
            'getFirstName' => $addressValues[0],
            'getLastName' => $addressValues[1],
            'getCountry' => $addressValues[2],
            'getCity' => $addressValues[3],
            'getState' => $addressValues[4],
            'getAddress' => $addressValues[5] . PHP_EOL . $addressValues[6],
            'getZip' => $addressValues[7],
            'getCompany' => $addressValues[8],
            'getPhoneNumber' => $addressValues[9]
        ];

        foreach ($addressMethods as $key => $method) {
            $this->customerAddress->expects(static::atLeastOnce())
                ->method($method)
                ->willReturn($addressValues[$key]);
        }

        $taxAmount = '0.85';

        $this->order->expects(static::once())
            ->method('getBaseTaxAmount')
            ->willReturn($taxAmount);

        $shippingAmount = '5';
        $shippingDescription = 'Flat Rate - Fixed';

        $this->order->expects(static::once())
            ->method('getBaseShippingAmount')
            ->willReturn($shippingAmount);

        $this->order->expects(static::exactly(2))
            ->method('getShippingDescription')
            ->willReturn($shippingDescription);

        $this->orderAdapter->expects(static::once())
            ->method('getItems')
            ->willReturn([]);

        $customerId = '10007';

        $this->orderAdapter->expects(static::once())
            ->method('getCustomerId')
            ->willReturn($customerId);

        $this->orderAdapter->expects(static::atLeastOnce())->method('getCurrencyCode')->willReturn('EUR');

        $this->orderAdapter->expects(static::exactly(2))
            ->method('getBillingAddress')
            ->willReturn($this->customerAddress);

        $this->orderAdapter->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->customerAddress);

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

        $requestResult = $this->requestBuilder->build(['payment' => $this->payment, 'amount' => $amount]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert transaction details
        static::assertEquals($this->transactionType, $requestObject->getTransactionRequest()->getTransactionType());
        static::assertEquals(sprintf('%.2F', $amount), $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals('EUR', $requestObject->getTransactionRequest()->getCurrencyCode());
        static::assertEquals($invoceNumber, $requestObject->getTransactionRequest()->getOrder()->getInvoiceNumber());
        foreach ($customerAddressMethods as $method => $value) {
            static::assertEquals($value, $requestObject->getTransactionRequest()->getBillTo()->$method());
        }

        foreach (array_diff_key($customerAddressMethods, ['getPhoneNumber' => '']) as $method => $value) {
            static::assertEquals($value, $requestObject->getTransactionRequest()->getShipTo()->$method());
        }
        static::assertEquals($customerId, $requestObject->getTransactionRequest()->getCustomer()->getId());
        static::assertEquals($addressValues[10], $requestObject->getTransactionRequest()->getCustomer()->getEmail());
        static::assertEquals($taxAmount, $requestObject->getTransactionRequest()->getTax()->getAmount());
        static::assertEquals($shippingAmount, $requestObject->getTransactionRequest()->getShipping()->getAmount());
        static::assertEquals($shippingDescription, $requestObject->getTransactionRequest()->getShipping()->getName());
        static::assertEquals(
            $shippingDescription,
            $requestObject->getTransactionRequest()->getShipping()->getDescription()
        );

        static::assertEquals($opaqueData['dataDescriptor'], $requestObject->getTransactionRequest()->getPayment()->getOpaqueData()->getDataDescriptor());
        static::assertEquals($opaqueData['dataValue'], $requestObject->getTransactionRequest()->getPayment()->getOpaqueData()->getDataValue());

        //make sure refId is not set
        static::assertEquals(null, $requestObject->getRefId());

        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    private function getPaymentDataObjectMock()
    {
        $this->order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = $this->getMockBuilder(Payment::class)
            ->setMethods(['getOrder', 'getMethodInstance'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->payment->expects(static::exactly(5))
            ->method('getOrder')
            ->willReturn($this->order);

        $this->orderAdapter = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDOMock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $paymentDOMock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $paymentDOMock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        return $paymentDOMock;
    }

    public function testPrepareSolutionId()
    {
        $this->configMock->expects(static::atLeastOnce())
            ->method('getSolutionId')
            ->willReturn(null);
        
        $methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();
        
        $this->assertEquals(null, $this->requestBuilderHelper->prepareSolutionId($methodInstanceMock));
    }
}
