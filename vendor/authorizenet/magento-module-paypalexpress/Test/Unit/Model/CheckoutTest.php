<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Model;

use AuthorizeNet\PayPalExpress\Gateway\Command\InitializeCommand;
use AuthorizeNet\PayPalExpress\Model\Checkout;
use PHPUnit\Framework\TestCase;
use net\authorize\api\contract\v1 as AnetAPI;

class CheckoutTest extends TestCase
{

    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Command\GetDetailsCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandMock;

    /**
     * @var \Magento\Quote\Model\QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Helper\AddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressConverterMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartManagementMock;

    /**
     * @var \Magento\Checkout\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutDataMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;
    
    /**
     * @var Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutModel;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    protected function setUp()
    {

        $this->commandMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Gateway\Command\GetDetailsCommand::class)->disableOriginalConstructor()->getMock();
        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository::class)->disableOriginalConstructor()->getMock();
        $this->addressConverterMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Gateway\Helper\AddressConverter::class)->disableOriginalConstructor()->getMock();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory::class)->disableOriginalConstructor()->getMock();
        $this->cartManagementMock = $this->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)->getMockForAbstractClass();
        $this->checkoutDataMock = $this->getMockBuilder(\Magento\Checkout\Helper\Data::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)->disableOriginalConstructor()->getMock();

        $this->checkoutSessionMock = $this->getCheckoutSessionMock();

        $this->quoteMock = $this
            ->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods([
                'getBaseGrandTotal',
                'getPayment',
                'isVirtual',
                'getBillingAddress',
                'getShippingAddress',
                'setCheckoutMethod',
                'getCheckoutMethod',
                'setBillingAddress',
                'getExtensionAttributes',
                'setShippingAddress',
                'collectTotals'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->checkoutSessionMock->expects(static::any())->method('getQuote')->willReturn($this->quoteMock);
        
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();

        $this->checkoutModel = new Checkout(
            $this->checkoutSessionMock,
            $this->commandMock,
            $this->paymentDataObjectFactoryMock,
            $this->quoteRepositoryMock,
            $this->addressConverterMock,
            $this->cartManagementMock,
            $this->checkoutDataMock,
            $this->customerSessionMock
        );
    }

    public function testUpdateShippingMethod()
    {

        $methodCode = 'my_method';

        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShippingMethod', 'setCollectShippingRates', 'getShippingMethod'])
            ->getMock();
        
        $this->quoteMock->expects(static::once())->method('isVirtual')->willReturn(false);
        $this->quoteMock->expects(static::once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        
        $cartExtension = $this->getMockBuilder(\Magento\Quote\Api\Data\CartExtension::class)
            ->setMethods(['getShippingAssignments'])
            ->disableOriginalConstructor()->getMock();

        $shipping = $this->getMockForAbstractClass(\Magento\Quote\Api\Data\ShippingInterface::class);
        
        $shippingAssignments = [
            $this->getMockForAbstractClass(\Magento\Quote\Api\Data\ShippingAssignmentInterface::class)
        ];
        
        $shippingAssignments[0]->expects(static::once())->method('getShipping')->willReturn($shipping);
        
        $this->quoteMock->expects(static::once())->method('getExtensionAttributes')->willReturn($cartExtension);
        
        $cartExtension->expects(static::atLeastOnce())->method('getShippingAssignments')->willReturn($shippingAssignments);
        
        $shippingAddressMock->expects(static::once())->method('setShippingMethod')->with($methodCode)->willReturnSelf();
        $shippingAddressMock->expects(static::once())->method('setCollectShippingRates')->with(true)->willReturnSelf();
        
        $shipping->expects(static::once())
            ->method('setMethod')
            ->with($methodCode);
        
        $this->quoteRepositoryMock->expects(static::once())->method('save')->with($this->quoteMock);

        $this->checkoutModel->updateShippingMethod($methodCode);
    }

    public function testUpdateShippingMethodVirtual()
    {

        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShippingMethod', 'setCollectShippingRates'])
            ->getMock();
        
        $this->quoteMock->expects(static::once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::once())->method('isVirtual')->willReturn(true);

        $shippingAddressMock->expects(static::never())->method('setShippingMethod');

        $this->checkoutModel->updateShippingMethod('some_code');
    }

    public function testUpdateShippingMethodIsSame()
    {

        $methodCode = 'some_code';
        
        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShippingMethod', 'setCollectShippingRates', 'getShippingMethod'])
            ->getMock();

        $this->quoteMock->expects(static::once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::once())->method('isVirtual')->willReturn(false);

        $shippingAddressMock->expects(static::once())->method('getShippingMethod')->willReturn($methodCode);
        $shippingAddressMock->expects(static::never())->method('setShippingMethod');

        $this->checkoutModel->updateShippingMethod($methodCode);
    }

    /**
     * @param $isLoggedIn
     * @param $isAllowedGuestCheckout
     * @param $expectedCheckoutMethod
     * @dataProvider dataProviderTestGetCheckoutMethod
     */
    public function testGetCheckoutMethod($isLoggedIn, $isAllowedGuestCheckout, $expectedCheckoutMethod)
    {

        $this->customerSessionMock->expects(static::any())
            ->method('isLoggedIn')
            ->willReturn($isLoggedIn);
        
        $this->checkoutDataMock->expects(static::any())
            ->method('isAllowedGuestCheckout')
            ->willReturn($isAllowedGuestCheckout);
        
        if (!$isLoggedIn) {
            $this->quoteMock->expects(static::at(0))
                ->method('getCheckoutMethod')
                ->willReturn(null);

            $this->quoteMock->expects(static::once())
                ->method('setCheckoutMethod')
                ->with($expectedCheckoutMethod)
                ->willReturnSelf();

            $this->quoteMock->expects(static::any())
                ->method('getCheckoutMethod')
                ->willReturn($expectedCheckoutMethod);
        }
        
        static::assertEquals($expectedCheckoutMethod, $this->checkoutModel->getCheckoutMethod());
    }

    public function dataProviderTestGetCheckoutMethod()
    {
        return [
            [
                'isLoggedIn' => false,
                'isAllowedGuestCheckout' => false,
                'expectedCheckoutMethod' => \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER
            ],
            [
                'isLoggedIn' => false,
                'isAllowedGuestCheckout' => true,
                'expectedCheckoutMethod' => \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST
            ],
            [
                'isLoggedIn' => true,
                'isAllowedGuestCheckout' => false,
                'expectedCheckoutMethod' => \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER
            ],
        ];
    }

    public function testPlace()
    {

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods([
                'getPayment',
                'getIsVirtual',
                'isVirtual',
                'getId',
                'getCheckoutMethod',
                'getShippingAddress',
                'getBillingAddress',
                'setCustomerId',
                'setCustomerEmail',
                'setCustomerIsGuest',
                'setCustomerGroupId',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSessionMock = $this->getCheckoutSessionMock();
        $this->checkoutSessionMock->expects(static::any())->method('getQuote')->willReturn($this->quoteMock);

        $this->checkoutModel = new Checkout(
            $this->checkoutSessionMock,
            $this->commandMock,
            $this->paymentDataObjectFactoryMock,
            $this->quoteRepositoryMock,
            $this->addressConverterMock,
            $this->cartManagementMock,
            $this->checkoutDataMock,
            $this->customerSessionMock
        );

        $this->quoteMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);
        $this->quoteMock->expects(static::any())->method('getIsVirtual')->willReturn(false);
        $this->quoteMock->expects(static::any())->method('isVirtual')->willReturn(false);

        $this->quoteMock->expects(static::any())->method('getCheckoutMethod')->willReturn(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        
        $billingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShouldIgnoreValidation', 'getEmail'])
            ->getMock();
        
        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShouldIgnoreValidation'])
            ->getMock();
        
        $email = 'test@example.org';
        
        $billingAddressMock->expects(static::once())->method('getEmail')->willReturn($email);

        $this->quoteMock->expects(static::any())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::any())->method('getBillingAddress')->willReturn($billingAddressMock);
        $this->quoteMock->expects(static::once())->method('setCustomerId')->with(null)->willReturnSelf();
        $this->quoteMock->expects(static::once())->method('setCustomerEmail')->with($email)->willReturnSelf();
        $this->quoteMock->expects(static::once())->method('setCustomerIsGuest')->with(true)->willReturnSelf();
        $this->quoteMock->expects(static::once())->method('setCustomerGroupId')->with(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID)->willReturnSelf();

        $this->paymentMock->expects(static::once())
            ->method('importData')
            ->with(['method' => \AuthorizeNet\PayPalExpress\Gateway\Config\Config::CODE])
            ->willReturnSelf();
        
        $quoteId = 123;
        
        $this->quoteMock->expects(static::once())->method('getId')->willReturn($quoteId);
            
        $this->cartManagementMock->expects(static::once())->method('placeOrder')->with($quoteId);

        $this->checkoutSessionMock->expects(static::once())->method('setData')->with(Checkout::KEY_HAS_DATA_FETCHED, false);

        $this->checkoutModel->place();
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Customer session must be set.
     */
    public function testGetCustomerSessionWithException()
    {
        $this->checkoutModel->setCustomerSession(null);
        $this->checkoutModel->getCustomerSession();
    }

    private function getCheckoutSessionMock()
    {
        return $this
            ->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['getQuote', 'setData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getCheckoutMockWithMethods(array $methods)
    {
        return $this->getMockBuilder(Checkout::class)
            ->setMethods($methods)
            ->setConstructorArgs([
                $this->checkoutSessionMock,
                $this->commandMock,
                $this->paymentDataObjectFactoryMock,
                $this->quoteRepositoryMock,
                $this->addressConverterMock,
                $this->cartManagementMock,
                $this->checkoutDataMock,
                $this->customerSessionMock
            ])
            ->getMock();
    }

    /**
     * @dataProvider dataProviderTestRetrievePaypalCheckoutData
     */
    public function testRetrievePaypalCheckoutData($dataRetrieved, $expectedInvocationCount)
    {
        $this->checkoutModel = $this->getCheckoutMockWithMethods([
            'hasCheckoutDataRetrieved',
            'setHasCheckoutDataRetrieved',
            'fetchPaypalCheckoutData',
            'updatePaypalCheckoutData'
        ]);


        $detailsMock = $this->getMockBuilder(AnetAPI\TransactionResponseType::class)->getMock();

        $this->checkoutModel->expects(static::any())->method('hasCheckoutDataRetrieved')->willReturn($dataRetrieved);
        $this->checkoutModel->expects(static::exactly($expectedInvocationCount))->method('fetchPaypalCheckoutData')->willReturn($detailsMock);
        $this->checkoutModel->expects(static::exactly($expectedInvocationCount))->method('setHasCheckoutDataRetrieved')->with(true);
        $this->checkoutModel->expects(static::exactly($expectedInvocationCount))->method('updatePaypalCheckoutData')->with($detailsMock);

        $this->checkoutModel->retrievePaypalCheckoutData();
    }

    public function dataProviderTestRetrievePaypalCheckoutData()
    {
        return [
            [
                'dataRetrieved' => true,
                'expectedInvocationCount' => 0,
            ],
            [
                'dataRetrieved' => false,
                'expectedInvocation' => 1,
            ],
        ];
    }

    public function testUpdatePaypalCheckoutData()
    {
        $email = 'test@example.org';
        $payerId = '1233232';

        $detailsMock = $this->getMockBuilder(AnetAPI\TransactionResponseType::class)->getMock();
        $shipToMock = $this->getMockBuilder(AnetAPI\NameAndAddressType::class)->getMock();
        $acceptanceMock = $this->getMockBuilder(AnetAPI\TransactionResponseType\SecureAcceptanceAType::class)->getMock();

        $detailsMock->expects(static::any())->method('getShipTo')->willReturn($shipToMock);
        $detailsMock->expects(static::any())->method('getSecureAcceptance')->willReturn($acceptanceMock);
        $acceptanceMock->expects(static::any())->method('getPayerEmail')->willReturn($email);
        $acceptanceMock->expects(static::any())->method('getPayerID')->willReturn($payerId);

        $convertedAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->getMockForAbstractClass();

        $this->addressConverterMock->expects(static::any())->method('paypalAddressToMagento')->with($shipToMock)->willReturn($convertedAddressMock);

        $convertedAddressMock->expects(static::once())->method('setEmail')->with($email);

        $billingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShouldIgnoreValidation', 'getEmail'])
            ->getMock();

        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShouldIgnoreValidation'])
            ->getMock();

        $this->quoteMock->expects(static::any())->method('setBillingAddress')->with($convertedAddressMock);
        $this->quoteMock->expects(static::any())->method('setShippingAddress')->with($convertedAddressMock);
        $this->quoteMock->expects(static::any())->method('isVirtual')->willReturn(false);

        $this->quoteMock->expects(static::any())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::any())->method('getBillingAddress')->willReturn($billingAddressMock);

        $this->paymentMock->expects(static::exactly(2))->method('setAdditionalInformation')->withConsecutive(
            [Checkout::KEY_PAYER_EMAIL, $email],
            [InitializeCommand::KEY_PAYER_ID, $payerId]
        );

        $this->quoteRepositoryMock->expects(static::once())->method('save')->with($this->quoteMock);
        $this->checkoutModel->updatePaypalCheckoutData($detailsMock);
    }
    public function testUpdatePaypalCheckoutDataEmpty()
    {
        $this->checkoutModel->updatePaypalCheckoutData(null);
        $this->quoteRepositoryMock->expects(static::never())->method('save');
    }

    /**
     * @dataProvider dataProviderTestHasCheckoutDataRetrieved
     */
    public function testHasCheckoutDataRetrieved($returnValue, $expectedValue)
    {
        $this->checkoutSessionMock->expects(static::once())->method('getData')->with(Checkout::KEY_HAS_DATA_FETCHED)->willReturn($returnValue);
        static::assertEquals($expectedValue, $this->checkoutModel->hasCheckoutDataRetrieved());
    }

    public function dataProviderTestHasCheckoutDataRetrieved()
    {
        return [
            [
                'returnValue' => true,
                'expectedValue' => true,
            ],
            [
                'returnValue' => 1,
                'expectedValue' => true,
            ],
            [
                'returnValue' => false,
                'expectedValue' => false,
            ],
        ];
    }

    public function testSetHasCheckoutDataRetrieved()
    {
        $value = 'somevalue';

        $this->checkoutSessionMock->expects(static::once())->method('setData')->with(Checkout::KEY_HAS_DATA_FETCHED, $value);

        static::assertEquals($this->checkoutModel, $this->checkoutModel->setHasCheckoutDataRetrieved($value));
    }

    public function testGetTokenData()
    {

        $grandTotal = 123;

        $this->quoteMock->expects(static::any())->method('getBaseGrandTotal')->willReturn($grandTotal);

        $tokenData = 'somedata';

        $this->checkoutSessionMock->expects(static::once())->method('getData')->with(Checkout::TOKEN_DATA_CACHE_KEY_PREFIX . $grandTotal)->willReturn($tokenData);

        static::assertEquals($tokenData, $this->checkoutModel->getTokenData());
    }

    public function testFetchPaypalCheckoutData()
    {
        $this->paymentMock->expects(static::once())->method('setMethod')->with(\AuthorizeNet\PayPalExpress\Gateway\Config\Config::CODE);

        $paymentDataObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)->disableOriginalConstructor()->getMock();

        $this->paymentDataObjectFactoryMock->expects(static::once())->method('create')->with($this->paymentMock)->willReturn($paymentDataObjectMock);

        $detailsMock = $this->getMockBuilder(AnetAPI\TransactionResponseType::class)->getMock();

        $this->commandMock->expects(static::once())->method('execute')->with(['payment' => $paymentDataObjectMock])->willReturn($detailsMock);

        static::assertEquals($detailsMock, $this->checkoutModel->fetchPaypalCheckoutData());
    }

    public function testSaveTokenData()
    {
        $tokenData = [
            'transId' => '1231242424',
            'token' => 'PDQWD12125',
        ];

        $grandTotal = 123;

        $this->quoteMock->expects(static::any())->method('getBaseGrandTotal')->willReturn($grandTotal);

        $this->paymentMock->expects(static::once())
            ->method('setAdditionalInformation')
            ->with(InitializeCommand::KEY_INIT_TRANSACTION_ID, $tokenData['transId']);

        $this->quoteRepositoryMock->expects(static::once())->method('save')->with($this->quoteMock);

        $this->checkoutSessionMock->expects(static::exactly(2))
            ->method('setData')
            ->withConsecutive(
                [Checkout::KEY_HAS_DATA_FETCHED , false],
                [Checkout::TOKEN_DATA_CACHE_KEY_PREFIX . $grandTotal, $tokenData]
            );

        static::assertEquals($this->checkoutModel, $this->checkoutModel->saveTokenData($tokenData));
    }
}
;
