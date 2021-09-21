<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Model;

use PHPUnit\Framework\TestCase;

class DecodeBillingAddressTest extends TestCase
{


    /**
     * @var \Magento\Quote\Model\QuoteRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteRepositoryMock;
    /**
     * @var \Magento\Payment\Gateway\CommandInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataObjectFactoryMock;
    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressConverter;

    /**
     * @var DecodeBillingAddress
     */
    protected $plugin;

    /**
     * @var \Magento\Quote\Api\Data\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \net\authorize\api\contract\v1\CustomerAddressType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vcAddressMock;

    protected function setUp()
    {
     
        $this->quoteRepositoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteRepository::class)->disableOriginalConstructor()->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->setMethods(['getShippingAddress', 'getTelephone'])->disableOriginalConstructor()->getMock();
        $this->commandMock = $this->getMockBuilder(\Magento\Payment\Gateway\CommandInterface::class)->getMockForAbstractClass();
        $this->dataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface::class)->setMethods(['create'])->getMockForAbstractClass();
        $this->addressConverter = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter::class)->disableOriginalConstructor()->getMock();
        $this->paymentMethodMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)->disableOriginalConstructor()->getMock();
        $this->vcAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CustomerAddressType::class)->disableOriginalConstructor()->getMock();

        $this->plugin = new DecodeBillingAddress(
            $this->quoteRepositoryMock,
            $this->commandMock,
            $this->dataObjectFactoryMock,
            $this->addressConverter
        );
    }

    public function testPrepareData()
    {
        $cartId = 101;
        $shippingTelephone = '123123123';
        $billingAddressNew = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->getMockForAbstractClass();
        
        $this->quoteRepositoryMock->expects(static::once())
            ->method('get')
            ->with($cartId)
            ->willReturn($this->quoteMock);
        
        $this->addressConverter->expects(static::once())
            ->method('visaToMagentoAddress')
            ->willReturn($billingAddressNew);
        
        $this->paymentMethodMock->expects(static::once())
            ->method('getMethod')
            ->willReturn(\AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE);

        $this->dataObjectFactoryMock->expects(static::once())
            ->method('create')
            ->with($this->paymentMethodMock)
            ->willReturnSelf();
        
        $this->vcAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CustomerAddressType::class)->disableOriginalConstructor()->getMock();
        
        $decodedData = [
            \AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_BILLING_INFO => $this->vcAddressMock,
        ];
        
        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturnSelf();
        
        $this->quoteMock->expects(static::once())
            ->method('getTelephone')
            ->willReturn($shippingTelephone);
        
        $this->commandMock->expects(static::once())
            ->method('execute')
            ->with(['payment'=> $this->dataObjectFactoryMock]);
        
        $this->paymentMethodMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(\AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_DECRYPTED_DATA)
            ->willReturn($decodedData);

        static::assertEquals(
            [$this->paymentMethodMock, $billingAddressNew],
            $this->plugin->prepareData($cartId, $this->paymentMethodMock, null)
        );
    }

    public function testPrepareDataWithNoBillingAddress()
    {
        $cartId = 101;
        $shippingTelephone = '123123123';
        $billingAddressNew = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->getMockForAbstractClass();

        $this->quoteRepositoryMock->expects(static::once())
            ->method('get')
            ->with($cartId)
            ->willReturn($this->quoteMock);


        $this->paymentMethodMock->expects(static::once())
            ->method('getMethod')
            ->willReturn(\AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE);

        $this->dataObjectFactoryMock->expects(static::once())
            ->method('create')
            ->with($this->paymentMethodMock)
            ->willReturnSelf();

        $this->vcAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CustomerAddressType::class)->disableOriginalConstructor()->getMock();

        $decodedData = [
        ];

        $this->commandMock->expects(static::once())
            ->method('execute')
            ->with(['payment'=> $this->dataObjectFactoryMock]);

        $this->paymentMethodMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(\AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_DECRYPTED_DATA)
            ->willReturn($decodedData);

        static::assertEquals(
            [$this->paymentMethodMock, null],
            $this->plugin->prepareData($cartId, $this->paymentMethodMock, null)
        );
    }
}
