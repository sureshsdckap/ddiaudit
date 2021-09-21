<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Model;

use AuthorizeNet\PayPalExpress\Gateway\Command\InitializeCommand;

class Checkout
{

    const TOKEN_DATA_CACHE_KEY_PREFIX = 'ec_token_data_';
    const KEY_HAS_DATA_FETCHED = 'ec_data_fetched';
    const KEY_PAYER_EMAIL = 'payerEmail';

    /**
     * @var $checkoutSession
     */
    protected $checkoutSession;
    /**
     * @var $detailsCommand
     */
    protected $detailsCommand;
    /**
     * @var $paymentDataObjectFactory
     */
    protected $paymentDataObjectFactory;
    /**
     * @var $quoteRepository
     */
    protected $quoteRepository;
    /**
     * @var $addressConverter
     */
    protected $addressConverter;
    /**
     * @var $checkoutData
     */
    protected $checkoutData;
    /**
     * @var $cartManagement
     */
    protected $cartManagement;
    /**
     * @var $customerSession
     */
    protected $customerSession;

    /**
     * Checkout Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \AuthorizeNet\PayPalExpress\Gateway\Command\GetDetailsCommand $detailsCommand
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \AuthorizeNet\PayPalExpress\Gateway\Helper\AddressConverter $addressConverter
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Checkout\Helper\Data $checkoutData
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \AuthorizeNet\PayPalExpress\Gateway\Command\GetDetailsCommand $detailsCommand,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactory $paymentDataObjectFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \AuthorizeNet\PayPalExpress\Gateway\Helper\AddressConverter $addressConverter,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->detailsCommand = $detailsCommand;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteRepository = $quoteRepository;
        $this->addressConverter = $addressConverter;
        $this->checkoutData = $checkoutData;
        $this->cartManagement = $cartManagement;
        $this->customerSession = $customerSession;
    }

    /**
     * Save token data
     *
     * @param array $tokenData
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveTokenData(array $tokenData)
    {

        if (!empty($tokenData['transId'])) {
            $this->getQuote()->getPayment()
                ->setAdditionalInformation(
                    InitializeCommand::KEY_INIT_TRANSACTION_ID,
                    $tokenData['transId']
                );
            $this->quoteRepository->save($this->getQuote());
        }

        if ($tokenData != $this->getTokenData()) {
            $this->setHasCheckoutDataRetrieved(false);
        }

        $this->checkoutSession->setData($this->getTokenDataCacheKey(), $tokenData);

        return $this;
    }

    /**
     * Get Token Data
     *
     * @return array|null
     */
    public function getTokenData()
    {
        return $this->checkoutSession->getData($this->getTokenDataCacheKey());
    }

    /**
     * Get token data cache key
     *
     * @return string
     */
    private function getTokenDataCacheKey()
    {
        return self::TOKEN_DATA_CACHE_KEY_PREFIX . $this->checkoutSession->getQuote()->getBaseGrandTotal();
    }

    /**
     * Check the Checkout session
     *
     * @return boolean
     */
    public function hasCheckoutDataRetrieved()
    {
        return (bool)$this->checkoutSession->getData(self::KEY_HAS_DATA_FETCHED);
    }

    /**
     * Set checkout data
     *
     * @param $value
     * @return Checkout
     */
    public function setHasCheckoutDataRetrieved($value)
    {
        $this->checkoutSession->setData(self::KEY_HAS_DATA_FETCHED, $value);
        return $this;
    }

    /**
     * Set Payment method code in Payment object
     *
     * @return array
     */
    public function fetchPaypalCheckoutData()
    {
        $payment = $this->getQuote()->getPayment();

        $payment->setMethod($this->getPaymentMethodCode());

        return $this->detailsCommand->execute(['payment' => $this->paymentDataObjectFactory->create($payment)]);
    }

    /**
     * Retrieve PayPal checkout data
     */
    public function retrievePaypalCheckoutData()
    {
        if ($this->hasCheckoutDataRetrieved()) {
            return;
        }

        $detail = $this->fetchPaypalCheckoutData();

        $this->setHasCheckoutDataRetrieved(true);

        $this->updatePaypalCheckoutData($detail);
    }

    /**
     * Update Paypal checkout Data
     *
     * @param  \net\authorize\api\contract\v1\TransactionResponseType|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return Checkout
     */
    public function updatePaypalCheckoutData(\net\authorize\api\contract\v1\TransactionResponseType $detail = null)
    {
        if (!$detail) {
            return $this;
        }

        $address = $this->addressConverter->paypalAddressToMagento($detail->getShipTo());

        if (!$address->getCountryId()) {
            $address->setCountryId('US'); //avoid quote corruption if empty address returned
        }

        if ($payerEmail = $detail->getSecureAcceptance()->getPayerEmail()) {
            $this->getQuote()->getPayment()->setAdditionalInformation(self::KEY_PAYER_EMAIL, $payerEmail);
            $address->setEmail($payerEmail);
        }

        $this->getQuote()->setBillingAddress($address);
        if (!$this->getQuote()->isVirtual()) {
            $this->getQuote()->setShippingAddress($address);
        }
        $this->setIgnoreAddressValidation(true);

        if ($payerId = $detail->getSecureAcceptance()->getPayerID()) {
            $this->getQuote()->getPayment()->setAdditionalInformation(InitializeCommand::KEY_PAYER_ID, $payerId);
        }

        $this->quoteRepository->save($this->getQuote());

        return $this;
    }

    /**
     * Update checkout data and place an order
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function place()
    {

        if ($this->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST) {
            $this->prepareGuest();
        }

        $this->setIgnoreAddressValidation(true);

        $this->getQuote()->getPayment()->importData(['method' => $this->getPaymentMethodCode()]);

        $this->cartManagement->placeOrder($this->getQuote()->getId());

        $this->setHasCheckoutDataRetrieved(false);

        return $this;
    }

    /**
     * Get checkout session quote data.
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->checkoutSession->getQuote();
    }

    /**
     * To ignore the address validation
     *
     * @param array
     * @return Checkout
     */
    private function setIgnoreAddressValidation($ignore)
    {

        $this->getQuote()->getBillingAddress()->setShouldIgnoreValidation($ignore);

        if (!$this->getQuote()->isVirtual()) {
            $this->getQuote()->getShippingAddress()->setShouldIgnoreValidation($ignore);
        }

        return $this;
    }

    /**
     * Set checkout method as Guest or Registered customer
     *
     * @return Checkout
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
     * Prepare data for guest user
     *
     * @return Checkout
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
     * Retrieve the Customer Session
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
     * Initialize the Customer Session
     *
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function setCustomerSession(\Magento\Customer\Model\Session $customerSession = null)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * Get PayPal Express payment method code
     *
     * @return string
     */
    public function getPaymentMethodCode()
    {
        return \AuthorizeNet\PayPalExpress\Gateway\Config\Config::CODE;
    }

    /**
     * Update the quote with selected Shipping Method
     *
     * @param  string $methodCode
     * @return void
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
}
