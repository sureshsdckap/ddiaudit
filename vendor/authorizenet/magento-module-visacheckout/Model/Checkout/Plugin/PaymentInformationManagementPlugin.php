<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Model\Checkout\Plugin;

use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Quote\Model\QuoteRepository;

/**
 * Class PaymentInformationManagementPlugin
 * @package AuthorizeNet\VisaCheckout\Model\Checkout\Plugin
 */
class PaymentInformationManagementPlugin
{
    /**
     * @var \AuthorizeNet\VisaCheckout\Model\DataHandler
     */
    protected $handler;

    /**
     * PaymentInformationManagementPlugin constructor.
     * @param \AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress $handler
     */
    public function __construct(
        \AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress $handler
    ) {
        $this->handler = $handler;
    }

    /**
     * Save payment information
     *
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $data = $this->handler->prepareData($cartId, $paymentMethod, $billingAddress);
        return array_merge([$cartId], $data);
    }
}
