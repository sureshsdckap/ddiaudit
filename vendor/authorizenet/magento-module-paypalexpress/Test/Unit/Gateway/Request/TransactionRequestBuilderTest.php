<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Request;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Gateway\Request\TransactionRequestBuilder;
use Magento\Payment\Gateway\Data\Order\OrderAdapter;
use Magento\Payment\Gateway\Data\PaymentDataObject;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Sales\Model\Order\Payment;
use PHPUnit\Framework\TestCase;

class TransactionRequestBuilderTest extends TestCase
{
    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var TransactionRequestBuilder
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
     * @var \Magento\Sales\Model\Order|MockObject
     */
    private $orderMock;

    /**
     * @var \Magento\Payment\Gateway\Data\AddressAdapterInterface|MockObject
     */
    private $billingAddressMock;

    /**
     * @var string
     */
    private $transactionType;

    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->transactionType = 'authCaptureTransaction';

        $this->requestBuilder = new TransactionRequestBuilder(
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

        $paypalInitTransId = '1n1tTran51D';

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayPalInitTransId')
            ->willReturn($paypalInitTransId);

        $paypalPayerId = 'payer1D';

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayPalPayerId')
            ->willReturn($paypalPayerId);

        $solutionId = '12451552';

        $this->configMock->expects(static::once())
            ->method('getSolutionId')
            ->willReturn($solutionId);

        $invoceNumber = '100001';

        $this->orderAdapter->expects(static::any())
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

        $taxAmount = 0.5;
        $this->orderMock->expects(static::any())->method('getBaseTaxAmount')->willReturn($taxAmount);

        $customerEmail = 'test@example.org';
        $this->billingAddressMock->expects(static::any())->method('getEmail')->willReturn($customerEmail);

        $customerId = 33;
        $this->orderAdapter->expects(static::any())->method('getCustomerId')->willReturn($customerId);

        $itemsData = [
            [
                'name' => 'some name',
                'sku' => 'mySKU',
                'qty' => 7,
                'description' => '',
                'base_price' => 2,
                'base_discount_amount' => 1.5,
            ],
        ];

        $itemMocks = $this->getOrderItemMocksArray($itemsData);

        $this->orderAdapter->expects(static::any())->method('getItems')->willReturn($itemMocks);

        $requestResult = $this->requestBuilder->build(['payment' => $this->payment]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert transaction details
        static::assertEquals($this->transactionType, $requestObject->getTransactionRequest()->getTransactionType());
        static::assertEquals($paypalInitTransId, $requestObject->getTransactionRequest()->getRefTransId());
        static::assertEquals($paypalPayerId, $requestObject->getTransactionRequest()->getPayment()->getPayPal()->getPayerID());
        static::assertEquals('EUR', $requestObject->getTransactionRequest()->getCurrencyCode());

        static::assertEquals($taxAmount, $requestObject->getTransactionRequest()->getTax()->getAmount());
        static::assertEquals($customerEmail, $requestObject->getTransactionRequest()->getCustomer()->getEmail());
        static::assertEquals($customerId, $requestObject->getTransactionRequest()->getCustomer()->getId());

        foreach ($requestObject->getTransactionRequest()->getLineItems() as $index => $item) {
            static::assertEquals($itemsData[$index]['name'], $item->getName());
            static::assertEquals($itemsData[$index]['sku'], $item->getItemId());
            static::assertEquals($itemsData[$index]['qty'], $item->getQuantity());
            static::assertEquals($itemsData[$index]['base_price'] - round($itemsData[$index]['base_discount_amount'] / $itemsData[$index]['qty'], 2), $item->getUnitPrice());
            static::assertNull($item->getTaxable()); //make sure taxable is null for paypal
        }

        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    private function getPaymentDataObjectMock()
    {

        $this->payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();
        $this->billingAddressMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)->getMockForAbstractClass();

        $this->payment->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->orderAdapter = $this->getMockBuilder(OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock = $this->getMockBuilder(PaymentDataObject::class)
            ->setMethods(['getOrder', 'getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())
            ->method('getOrder')
            ->willReturn($this->orderAdapter);

        $mock->expects(static::once())
            ->method('getPayment')
            ->willReturn($this->payment);

        $this->orderAdapter->expects(static::any())->method('getBillingAddress')->willReturn($this->billingAddressMock);

        $this->payment->expects(static::any())->method('getOrder')->willReturn($this->orderMock);

        return $mock;
    }

    public function testAddressBuild()
    {

        /** @var TransactionRequestBuilder|MockObject $requestBuilderMock */
        $requestBuilderMock = $this->getMockBuilder(TransactionRequestBuilder::class)
            ->setConstructorArgs([
                $this->configMock,
                $this->subjectReaderMock,
                $this->transactionType
            ])
            ->setMethods(['prepareAddressData', 'prepareLineItems'])
            ->getMock();

        $subject['payment'] =  $this->getPaymentDataObjectMock();

        $this->subjectReaderMock->expects(static::once())
            ->method('readPayment')
            ->willReturn($subject['payment']);

        $orderAddressMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)->getMockForAbstractClass();
        $anetAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CustomerAddressType::class)->getMockForAbstractClass();

        $this->orderAdapter->expects(static::any())->method('getItems')->willReturn([]);
        $this->orderAdapter->expects(static::any())->method('getShippingAddress')->willReturn($orderAddressMock);

        $requestBuilderMock->expects(static::any())->method('prepareLineItems')->willReturn([]);
        $requestBuilderMock->expects(static::any())->method('prepareAddressData')->with($orderAddressMock, true)->willReturn($anetAddressMock);


        $requestResult = $requestBuilderMock->build($subject);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        static::assertSame($anetAddressMock, $requestObject->getTransactionRequest()->getShipTo(), 'shipping address is not the same');
    }


    private function getOrderItemMocksArray($items)
    {

        $mockItems = [];

        foreach ($items as $item) {
            $itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)->disableOriginalConstructor()
                ->setMethods([
                    'getName',
                    'getSku',
                    'getQtyOrdered',
                    'getDescription',
                    'getBasePrice',
                    'getBaseDiscountAmount'
                ])->getMock();

            $itemMock->expects(static::any())->method('getName')->willReturn($item['name']);
            $itemMock->expects(static::any())->method('getSku')->willReturn($item['sku']);
            $itemMock->expects(static::any())->method('getQtyOrdered')->willReturn($item['qty']);
            $itemMock->expects(static::any())->method('getDescription')->willReturn($item['description']);
            $itemMock->expects(static::any())->method('getBasePrice')->willReturn($item['base_price']);
            $itemMock->expects(static::any())->method('getBaseDiscountAmount')->willReturn($item['base_discount_amount']);
            $mockItems[] = $itemMock;
        }

        return $mockItems;
    }
}
