<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Model;

use AuthorizeNet\VisaCheckout\Gateway\Response\DecryptPaymentDataResponseHandler as ResponseHandler;
use net\authorize\api\contract\v1\CustomerAddressType;

class Checkout
{
    const PARAM_CALL_ID = 'callId';
    const PARAM_ENC_PAYMENT_DATA = 'encPaymentData';
    const PARAM_ENC_KEY = 'encKey';

    /**
     * @var \Magento\Payment\Gateway\CommandInterface
     */
    protected $decodeCommand;

    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter
     */
    protected $addressConverter;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutData;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    
    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config
     */
    protected $gatewayConfig;

    /**
     * Checkout Constructor
     *
     * @param \Magento\Payment\Gateway\CommandInterface $decodeCommand
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter $addressConverter
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface $dataObjectFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \AuthorizeNet\VisaCheckout\Gateway\Config\Config $gatewayConfig
     */
    public function __construct(
        \Magento\Payment\Gateway\CommandInterface $decodeCommand,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \AuthorizeNet\VisaCheckout\Gateway\Helper\AddressConverter $addressConverter,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface $dataObjectFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        \AuthorizeNet\VisaCheckout\Gateway\Config\Config $gatewayConfig
    ) {
        $this->decodeCommand = $decodeCommand;
        $this->quoteRepository = $quoteRepository;
        $this->addressConverter = $addressConverter;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->cartManagement = $cartManagement;
        $this->checkoutData = $checkoutData;
        $this->gatewayConfig = $gatewayConfig;
    }

    /**
     * Set quote data
     *
     * @param \Magento\Quote\Model\Quote|null
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote = null)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Get quote data
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {

        if (!$this->quote instanceof \Magento\Quote\Model\Quote) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Quote must be set.'));
        }

        return $this->quote;
    }

    /**
     * Save VC tokens
     *
     * Update payment additional information with required data.
     * Set billing and shipping information in quote repository.
     *
     * @param $callId
     * @param $encKey
     * @param $encData
     * @throws \Magento\Payment\Gateway\Command\CommandException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveVcTokens($callId, $encKey, $encData)
    {

        $payment = $this->getQuote()->getPayment();

        if (!$payment->getQuote()) {
            $payment->setQuote($this->getQuote());
        }

        $payment->importData(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE]);

        $payment
            ->setAdditionalInformation(self::PARAM_ENC_KEY, $encKey)
            ->setAdditionalInformation(self::PARAM_ENC_PAYMENT_DATA, $encData)
            ->setAdditionalInformation(self::PARAM_CALL_ID, $callId);

        $this->decodeCommand->execute(['payment' => $this->dataObjectFactory->create($payment)]);

        if (!$decryptedData = $payment->getAdditionalInformation(ResponseHandler::DATA_KEY_DECRYPTED_DATA)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Can\'t get decrypted information from Visa checkout.')
            );
        }

        if ($billingAddress = $this->getVcAddress($decryptedData, ResponseHandler::DATA_KEY_BILLING_INFO)) {
            $this->getQuote()->setBillingAddress($billingAddress);
        }

        if (!$this->getQuote()->isVirtual()) {
            $shippingAddress = $this->getVcAddress($decryptedData, ResponseHandler::DATA_KEY_SHIPPING_INFO);
            if ($shippingAddress) {
                $this->getQuote()->setShippingAddress($shippingAddress);
                $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            }
        }

        $this->getQuote()->collectTotals();
        $this->quoteRepository->save($this->getQuote());
    }

    /**
     * Get VC address
     *
     * @param $decryptedData
     * @param $dataKey
     * @return \Magento\Quote\Api\Data\AddressInterface|null
     */
    public function getVcAddress($decryptedData, $dataKey)
    {
        if (!isset($decryptedData[$dataKey]) || !$decryptedData[$dataKey] instanceof CustomerAddressType) {
            return null;
        }

        return $this->addressConverter->visaToMagentoAddress($decryptedData[$dataKey]);
    }
    
    /**
     * Get VC Call id
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getVcCallId()
    {
        return $this->getQuote()->getPayment()->getAdditionalInformation(self::PARAM_CALL_ID);
    }

    /**
     * Update shipping method in quote.
     *
     * @param $methodCode
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateShippingMethod($methodCode)
    {
        $quote = $this->getQuote();
        $shippingAddress = $quote->getShippingAddress();

        if ($quote->isVirtual() || !$shippingAddress) {
            return;
        }

        if ($methodCode == $shippingAddress->getShippingMethod()) {
            return;
        }

        $shippingAddress->setShippingMethod($methodCode)->setCollectShippingRates(true);

        $cartExtension = $quote->getExtensionAttributes();
        if ($cartExtension && $cartExtension->getShippingAssignments()) {
            $cartExtension->getShippingAssignments()[0]
                ->getShipping()
                ->setMethod($methodCode);
        }

        $quote->collectTotals();
        $this->quoteRepository->save($quote);
    }

    /**
     * Update billing address data
     *
     * @param  array $data
     * @return array $this
     */
    public function updateBillingAddressData($data)
    {
        $address = $this->getQuote()->getBillingAddress();
        
        $address->addData($data);
        
        return $this;
    }

    /**
     * Update shipping address
     *
     * @param  array $data
     * @return array $this
     */
    public function updateShippingAddressData($data)
    {
        $address = $this->getQuote()->getShippingAddress();

        $address->addData($data);

        return $this;
    }

    /**
     * Get checkout method
     *
     * @return array
     */
    public function getCheckoutMethod()
    {
        if ($this->getCustomerSession()->isLoggedIn()) {
            return \Magento\Checkout\Model\Type\Onepage::METHOD_CUSTOMER;
        }
        if (!$this->getQuote()->getCheckoutMethod()) {
            if ($this->checkoutData->isAllowedGuestCheckout($this->getQuote())) {
                $this->getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            } else {
                $this->getQuote()->setCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            }
        }
        return $this->getQuote()->getCheckoutMethod();
    }

    /**
     * Prepar for guest
     *
     * @return array $this
     */
    public function prepareGuest()
    {
        $quote = $this->getQuote();

        $quote->setCustomerId(null)
            ->setCustomerEmail($quote->getBillingAddress()->getEmail())
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Update quote and place order
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function place()
    {

        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuest();
        }
        
        if (!$this->gatewayConfig->isTelephoneRequired()) {
            $this->getQuote()->getBillingAddress()->setShouldIgnoreValidation(true);
            if (!$this->getQuote()->getIsVirtual()) {
                $this->getQuote()->getShippingAddress()->setShouldIgnoreValidation(true);
            }
        }

        $this->getQuote()->getPayment()->importData(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE]);

        $this->cartManagement->placeOrder($this->getQuote()->getId());

        return $this;
    }

    /**
     * Get customer session
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        if (!$this->customerSession instanceof \Magento\Customer\Model\Session) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Customer session must be set.'));
        }

        return $this->customerSession;
    }

    /**
     * Set customer session
     *
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function setCustomerSession(\Magento\Customer\Model\Session $customerSession = null)
    {
        $this->customerSession = $customerSession;
    }
}
