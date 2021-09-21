<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Model;

use AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler as ResponseHandler;
use AuthorizeNet\VisaCheckout\Model\Checkout;
use net\authorize\api\contract\v1\CustomerAddressType;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{

    /**
     * @var \Magento\Payment\Gateway\CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandMock;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressConverterMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
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
     * @var Checkout
     */
    protected $checkoutModel;
    

    protected function setUp()
    {

        $this->commandMock = $this->getMockBuilder(\Magento\Payment\Gateway\CommandInterface::class)->getMockForAbstractClass();

        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Api\CartRepositoryInterface::class)->getMockForAbstractClass();

        $this->addressConverterMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter::class)->disableOriginalConstructor()->getMock();

        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface::class)->getMockForAbstractClass();

        $this->cartManagementMock = $this->getMockBuilder(\Magento\Quote\Api\CartManagementInterface::class)->getMockForAbstractClass();

        $this->checkoutDataMock = $this->getMockBuilder(\Magento\Checkout\Helper\Data::class)->disableOriginalConstructor()->getMock();

        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)->disableOriginalConstructor()->getMock();

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();
        $this->quoteMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($this->paymentMock);
        
        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();

        $this->checkoutModel = new Checkout(
            $this->commandMock,
            $this->quoteRepositoryMock,
            $this->addressConverterMock,
            $this->paymentDataObjectFactoryMock,
            $this->cartManagementMock,
            $this->checkoutDataMock,
            $this->configMock
        );

        $this->prepareQuote();
    }

    private function prepareQuote()
    {
        $this->checkoutModel->setQuote($this->quoteMock);
    }
    
    private function prepareCustomerSession()
    {
        $this->checkoutModel->setCustomerSession($this->customerSessionMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Quote must be set.
     * @covers \AuthorizeNet\VisaCheckout\Model\Checkout::getQuote
     */
    public function testGetQuoteWithException()
    {
        $this->checkoutModel->setQuote(null);
        $this->checkoutModel->prepareGuest();
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function testSaveVcTokens()
    {

        $callId = '2315125235';
        $encDataKey = 'p32o2pgn4ng34g';
        $encData = 'oqmgo4mgomq3o4g';

        $this->paymentMock->expects(static::exactly(3))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [Checkout::PARAM_ENC_KEY, $encDataKey],
                [Checkout::PARAM_ENC_PAYMENT_DATA, $encData],
                [Checkout::PARAM_CALL_ID, $callId]
            )->willReturnSelf();

        $this->paymentMock->expects(static::once())
            ->method('importData')
            ->with(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE])
            ->willReturnSelf();

        $paymentDOMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)->disableOriginalConstructor()->getMock();
        $this->paymentDataObjectFactoryMock->expects(static::once())
            ->method('create')
            ->with($this->paymentMock)
            ->willReturn($paymentDOMock);

        $this->commandMock->expects(static::once())
            ->method('execute')
            ->with(['payment' => $paymentDOMock]);

        $decryptedData = [
            ResponseHandler::DATA_KEY_BILLING_INFO => $this->getMockBuilder(CustomerAddressType::class)->getMock(),
            ResponseHandler::DATA_KEY_SHIPPING_INFO => $this->getMockBuilder(CustomerAddressType::class)->getMock(),
        ];

        $this->paymentMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(ResponseHandler::DATA_KEY_DECRYPTED_DATA)
            ->willReturn($decryptedData);

        $billingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->disableOriginalConstructor()->getMock();
        $shippingAddressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCollectShippingRates'])
            ->getMock();

        $this->addressConverterMock->expects(static::at(0))
            ->method('visaToMagentoAddress')
            ->with($decryptedData[ResponseHandler::DATA_KEY_BILLING_INFO])
            ->willReturn($billingAddressMock);

        $this->addressConverterMock->expects(static::at(1))
            ->method('visaToMagentoAddress')
            ->with($decryptedData[ResponseHandler::DATA_KEY_SHIPPING_INFO])
            ->willReturn($shippingAddressMock);

        $this->quoteMock->expects(static::once())->method('setBillingAddress')->with($billingAddressMock)->willReturnSelf();
        $this->quoteMock->expects(static::once())->method('setShippingAddress')->with($shippingAddressMock)->willReturnSelf();
        $this->quoteMock->expects(static::once())->method('getShippingAddress')->willReturn($shippingAddressMock);
        $this->quoteMock->expects(static::once())->method('collectTotals')->willReturnSelf();

        $shippingAddressMock->expects(static::once())->method('setCollectShippingRates')->with(true)->willReturnSelf();

        $this->quoteRepositoryMock->expects(static::once())->method('save')->with($this->quoteMock);

        $this->checkoutModel->saveVcTokens($callId, $encDataKey, $encData);
    }


    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Can't get decrypted information from Visa checkout.
     */
    public function testSaveVcTokensWithException()
    {
        $callId = '2315125235';
        $encDataKey = 'p32o2pgn4ng34g';
        $encData = 'oqmgo4mgomq3o4g';

        $this->paymentMock->expects(static::exactly(3))
            ->method('setAdditionalInformation')
            ->withConsecutive(
                [Checkout::PARAM_ENC_KEY, $encDataKey],
                [Checkout::PARAM_ENC_PAYMENT_DATA, $encData],
                [Checkout::PARAM_CALL_ID, $callId]
            )->willReturnSelf();

        $this->paymentMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(ResponseHandler::DATA_KEY_DECRYPTED_DATA)
            ->willReturn(null);
        
        $this->checkoutModel->saveVcTokens($callId, $encDataKey, $encData);
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
        $this->prepareCustomerSession();
        
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
        $this->prepareCustomerSession();

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods([
                'getPayment',
                'getIsVirtual',
                'getId',
                'getCheckoutMethod',
                'getShippingAddress',
                'getBillingAddress',
                'setCustomerId',
                'setCustomerEmail',
                'setCustomerIsGuest',
                'setCustomerGroupId'
            ])
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->prepareQuote();
        
        $this->quoteMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);
        $this->quoteMock->expects(static::any())->method('getIsVirtual')->willReturn(false);
        
        $this->quoteMock->expects(static::any())->method('getCheckoutMethod')->willReturn(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
        
        $this->configMock->expects(static::once())->method('isTelephoneRequired')->willReturn(false);

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
            ->with(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE])
            ->willReturnSelf();
        
        $quoteId = 123;
        
        $this->quoteMock->expects(static::once())->method('getId')->willReturn($quoteId);
            
        $this->cartManagementMock->expects(static::once())->method('placeOrder')->with($quoteId);

        $this->checkoutModel->place();
    }

    public function testUpdateAddresses()
    {
        $addressData = ['street' => 'someStreet'];
        $addressMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)->disableOriginalConstructor()->getMock();
        
        $this->quoteMock->expects(static::once())->method('getBillingAddress')->willReturn($addressMock);
        $this->quoteMock->expects(static::once())->method('getShippingAddress')->willReturn($addressMock);
        
        $addressMock->expects(static::exactly(2))->method('addData')->with($addressData);
        
        $this->checkoutModel->updateShippingAddressData($addressData);
        $this->checkoutModel->updateBillingAddressData($addressData);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Customer session must be set.
     */
    public function testGetCustomerSessionWithException()
    {
        $this->checkoutModel->getCustomerSession();
    }
    
    public function testGetVcAddressWithoutData()
    {
        $data = [];
        static::assertEquals(null, $this->checkoutModel->getVcAddress($data, ResponseHandler::DATA_KEY_BILLING_INFO));
    }

    public function testGetVcCallId()
    {
        $callId = '1212312312312';
        $this->paymentMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(Checkout::PARAM_CALL_ID)
            ->willReturn($callId);
        
        static::assertEquals($callId, $this->checkoutModel->getVcCallId());
    }
}
