<?php

namespace AuthorizeNet\VisaCheckout\Test\Unit\Model\Checkout\Plugin;

use AuthorizeNet\VisaCheckout\Model\Checkout\Plugin\GuestPaymentInformationManagementPlugin;
use PHPUnit\Framework\TestCase;

class GuestPaymentInformationManagementPluginTest extends TestCase
{
    /**
     * @var PaymentInformationManagementPlugin
     */
    protected $plugin;

    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $handlerMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress::class)->disableOriginalConstructor()->getMock();
        $quoteIdMaskFactoryMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMaskFactory::class)->disableOriginalConstructor()->getMock();
        $quoteIdMaskMock = $this->getMockBuilder(\Magento\Quote\Model\QuoteIdMask::class)->setMethods(['load', 'getQuoteId'])->disableOriginalConstructor()->getMock();
        $subjectMock = $this->getMockBuilder(\Magento\Checkout\Api\GuestPaymentInformationManagementInterface::class)->disableOriginalConstructor()->getMock();
        $paymentMethodMock = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentInterface::class)->disableOriginalConstructor()->getMock();
        $billingAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->disableOriginalConstructor()->getMock();


        $this->plugin = new GuestPaymentInformationManagementPlugin(
            $quoteIdMaskFactoryMock,
            $handlerMock
        );

        $cartIdMask = 'mask100';
        $email = 'test@email.com';
        $cartId = 100;

        $quoteIdMaskFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($quoteIdMaskMock);

        $quoteIdMaskMock->expects(static::once())
            ->method('load')
            ->with($cartIdMask, 'masked_id')
            ->willReturnSelf();

        $quoteIdMaskMock->expects(static::once())
            ->method('getQuoteId')
            ->willReturn($cartId);

        $handlerMock->expects(static::once())
            ->method('prepareData')
            ->with($cartId, $paymentMethodMock, null)
            ->willReturn([$paymentMethodMock, $billingAddressMock]);

        static::assertEquals(
            [$cartIdMask, $email, $paymentMethodMock, $billingAddressMock],
            $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
                $subjectMock,
                $cartIdMask,
                $email,
                $paymentMethodMock,
                null
            )
        );
    }
}
