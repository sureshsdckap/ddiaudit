<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Block\Checkout;

use Magento\Framework\View\Element\Template;

abstract class AbstractReview extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    protected $addressConfig;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;

    /**
     * @var \Magento\Tax\Helper\Data
     */
    protected $taxHelper;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var $currentShippingRate
     */
    protected $currentShippingRate;

    /**
     * @var $shippingRateGroups
     */
    protected $shippingRateGroups;

    /**
     * AbstractReview Constructor
     *
     * @param Template\Context                                  $context
     * @param \Magento\Tax\Helper\Data                          $taxHelper
     * @param \Magento\Customer\Model\Address\Config            $addressConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {

        $this->taxHelper = $taxHelper;
        $this->addressConfig = $addressConfig;
        $this->priceCurrency = $priceCurrency;

        parent::__construct($context, $data);
    }

    /**
     *  Get Quote object.
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set Quote object.
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return Review
     */
    public function setQuote(\Magento\Quote\Model\Quote $quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * Get the shipping address details
     *
     * @return false|\Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        if ($this->getQuote()->getIsVirtual()) {
            return false;
        }
        return $this->getQuote()->getShippingAddress();
    }

    /**
     * Get the Billing address details
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    /**
     * To render the address data
     *
     * @param $address
     * @return array
     */
    public function renderAddress($address)
    {
        /** @var \Magento\Customer\Block\Address\Renderer\RendererInterface $renderer */
        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = \Magento\Framework\Convert\ConvertArray::toFlatArray($address->getData());
        return $renderer->renderArray($addressData);
    }

    /**
     *  Get the Shipping Method details
     *
     * @return array
     */
    public function getShippingMethodHtml()
    {
        $this->setTemplate($this->getShippingMethodTemplate());
        return $this->toHtml();
    }

    /**
     * Declared abstract function
     *
     * @return text
     */
    abstract public function getShippingMethodTemplate();

    /**
     *  Get the Payment Method details
     *
     * @return array
     */
    public function getPaymentMethodTitle()
    {
        return $this->getQuote()->getPayment()->getMethodInstance()->getTitle();
    }

    /**
     *  Get the Customer Billing Email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getBillingAddress()->getEmail();
    }

    /**
     *  Get the Shipping Rate Group
     *
     * @return array
     */
    public function getShippingRateGroups()
    {
        if ($this->shippingRateGroups === null) {
            $this->shippingRateGroups = $this->getQuote()->getShippingAddress()->getGroupedAllShippingRates();
        }

        return $this->shippingRateGroups;
    }

    /**
     *  Get the current shipping rate
     *
     * @return \Magento\Quote\Model\Quote\Address\Rate|null
     */
    public function getCurrentShippingRate()
    {
        if ($this->currentShippingRate === null) {
            foreach ($this->getShippingRateGroups() as $group) {
                /** @var \Magento\Quote\Model\Quote\Address\Rate $rate */
                foreach ($group as $rate) {
                    if ($rate->getCode() == $this->getQuote()->getShippingAddress()->getShippingMethod()) {
                        $this->currentShippingRate = $rate;
                        break 2;
                    }
                }
            }
        }
        return $this->currentShippingRate;
    }

    /**
     *  Get the Carrier name
     *
     * @return string
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue(
            "carriers/{$carrierCode}/title",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return $name;
        }
        return $carrierCode;
    }

    /**
     *  Get the shipping price
     *
     * @return float
     */
    protected function getShippingPrice($price, $isInclTax)
    {
        return $this->formatPrice($this->taxHelper->getShippingPrice(
            $price,
            $isInclTax,
            $this->getQuote()->getShippingAddress()
        ));
    }

    /*
    * Declared abstract function.
    */
    abstract public function getShippingMethodSubmitUrl();

    /**
     * To format the shipping price
     *
     * @param $price
     */
    protected function formatPrice($price)
    {
        return $this->priceCurrency->convertAndFormat(
            $price,
            true,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        );
    }

    /**
     * Mange get shpping rate
     *
     * This method executes to get either shipping rate code or empty value on error.
     *
     * @param $rate
     * @return float
     */
    public function renderShippingRateValue(\Magento\Framework\DataObject $rate)
    {
        if ($rate->getErrorMessage()) {
            return '';
        }
        return $rate->getCode();
    }

    /**
     * To render the shipping rate options
     *
     * @param $rate
     * @param $format
     * @param $inclTaxFormat
     * @return string
     */
    public function renderShippingRateOption($rate, $format = '%s - %s%s', $inclTaxFormat = ' (%s %s)')
    {
        $renderedInclTax = '';
        if ($rate->getErrorMessage()) {
            $price = $rate->getErrorMessage();
        } else {
            $price = $this->getShippingPrice(
                $rate->getPrice(),
                $this->taxHelper->displayShippingPriceIncludingTax()
            );

            $incl = $this->getShippingPrice($rate->getPrice(), true);
            if ($incl != $price && $this->taxHelper->displayShippingBothPrices()) {
                $renderedInclTax = sprintf($inclTaxFormat, $this->escapeHtml(__('Incl. Tax')), $incl);
            }
        }
        return sprintf($format, $this->escapeHtml($rate->getMethodTitle()), $price, $renderedInclTax);
    }
    
    /**
     * Declared abstract function
     *
     */
    abstract public function getPlaceOrderUrl();
}
