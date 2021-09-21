<?php

namespace AuthorizeNet\VisaCheckout\Test\Unit\Model\Checkout\Plugin;

use AuthorizeNet\VisaCheckout\Model\Checkout\Plugin\PaymentInformationManagementPlugin;
use PHPUnit\Framework\TestCase;

class PaymentInformationManagementPluginTest extends TestCase
{
    /**
     * @var PaymentInformationManagementPlugin
     */
    protected $plugin;

    public function testBeforeSavePaymentInformationAndPlaceOrder()
    {
        $handlerMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress::class)->disableOriginalConstructor()->getMock();
        $subjectMock = $this->getMockBuilder(\Magento\Checkout\Api\PaymentInformationManagementInterface::class)->disableOriginalConstructor()->getMock();
        $paymentMethodMock = $this->getMockBuilder(\Magento\Quote\Api\Data\PaymentInterface::class)->disableOriginalConstructor()->getMock();
        $billingAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->disableOriginalConstructor()->getMock();


        $this->plugin = new PaymentInformationManagementPlugin(
            $handlerMock
        );

        $cartId = 100;

        $handlerMock->expects(static::once())
            ->method('prepareData')
            ->with($cartId, $paymentMethodMock, null)
            ->willReturn([$paymentMethodMock, $billingAddressMock]);

        static::assertEquals(
            [$cartId, $paymentMethodMock, $billingAddressMock],
            $this->plugin->beforeSavePaymentInformationAndPlaceOrder(
                $subjectMock,
                $cartId,
                $paymentMethodMock,
                null
            )
        );
    }
}
