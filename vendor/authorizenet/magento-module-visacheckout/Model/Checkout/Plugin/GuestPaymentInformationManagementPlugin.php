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
 * Class GuestPaymentInformationManagementPlugin
 * @package AuthorizeNet\VisaCheckout\Model\Checkout\Plugin
 */
class GuestPaymentInformationManagementPlugin
{

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \AuthorizeNet\VisaCheckout\Model\DataHandler
     */
    protected $handler;

    /**
     * GuestPaymentInformationManagementPlugin constructor.
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress $handler
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \AuthorizeNet\VisaCheckout\Model\DecodeBillingAddress $handler
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->handler = $handler;
    }

    /**
     * Save payment information
     *
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param $cartId
     * @param $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $data = $this->handler->prepareData($quoteIdMask->getQuoteId(), $paymentMethod, $billingAddress);
        return array_merge([$cartId, $email], $data);
    }
}
