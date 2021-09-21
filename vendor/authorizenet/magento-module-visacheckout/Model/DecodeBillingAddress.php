<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Model;

use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Quote\Model\QuoteRepository;

class DecodeBillingAddress
{
    /**
     * @var $quoteRepository
     */
    protected $quoteRepository;
    /**
     * @var $checkoutDataDecodeCommand
     */
    protected $checkoutDataDecodeCommand;
    /**
     * @var $paymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;
    /**
     * @var $addressConverter
     */
    protected $addressConverter;

    /**
     * DecodeBillingAddress Constructor
     *
     * @param QuoteRepository $quoteRepository
     * @param \Magento\Payment\Gateway\CommandInterface $checkoutDataDecodeCommand
     * @param PaymentDataObjectFactoryInterface $dataObjectFactory
     * @param \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter $addressConverter
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        \Magento\Payment\Gateway\CommandInterface $checkoutDataDecodeCommand,
        PaymentDataObjectFactoryInterface $dataObjectFactory,
        \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter $addressConverter
    ) {

        $this->checkoutDataDecodeCommand = $checkoutDataDecodeCommand;
        $this->quoteRepository = $quoteRepository;
        $this->paymentDataObjectFactory = $dataObjectFactory;
        $this->addressConverter = $addressConverter;
    }

    /**
     * Save payment information
     *
     * Save payment method and update the quote.
     * Decode billing address and return payment and billing information.
     *
     * @param $quoteId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function prepareData(
        $quoteId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {

        if ($paymentMethod->getMethod() == \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE && $billingAddress === null) {
            $quote = $this->quoteRepository->get($quoteId);
            
            if ($paymentMethod instanceof \Magento\Quote\Model\Quote\Payment) {
                $paymentMethod->setQuote($quote);
            }
            
            $additionalData = $paymentMethod->getAdditionalData();

            foreach (['callId', 'encKey', 'encPaymentData'] as $key) {
                if (isset($additionalData[$key])) {
                    $paymentMethod->setAdditionalInformation($key, $additionalData[$key]);
                }
            }
            
            $this->checkoutDataDecodeCommand->execute(
                ['payment' => $this->paymentDataObjectFactory->create($paymentMethod)]
            );
            
            $billingAddress = $this->decodeBillingAddress($paymentMethod, $quote);
        };
        
        return [$paymentMethod, $billingAddress];
    }

    /**
     * Decode the billing address info
     *
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    private function decodeBillingAddress(\Magento\Quote\Api\Data\PaymentInterface $paymentMethod, \Magento\Quote\Api\Data\CartInterface $quote)
    {
        
        $decodedData = $paymentMethod->getAdditionalInformation(
            \AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_DECRYPTED_DATA
        );
        
        if (!$decodedData || !isset($decodedData[\AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_BILLING_INFO])) {
            return null;
        }

        /** @var \net\authorize\api\contract\v1\CustomerAddressType $addressData */
        $addressData = $decodedData[\AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler::DATA_KEY_BILLING_INFO];
        
        $billingAddress = $this->addressConverter->visaToMagentoAddress($addressData);
        
        if (!$billingAddress->getTelephone() && $quote instanceof \Magento\Quote\Model\Quote) {
            // ugly workaround for auth.net api limitation @see https://community.developer.authorize.net/t5/Integration-and-Testing/Visa-Checkout-Button-dataLevel-FULL-not-returning-any-more-than/td-p/55768
            $billingAddress->setTelephone($quote->getShippingAddress()->getTelephone());
        }
            
        return $billingAddress;
    }
}
