<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Review extends \AuthorizeNet\Core\Block\Checkout\AbstractReview
{

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config
     */
    protected $gatewayConfig;

    /**
     * Review Constructor
     *
     * @param Template\Context $context
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \AuthorizeNet\VisaCheckout\Gateway\Config\Config $gatewayConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \AuthorizeNet\VisaCheckout\Gateway\Config\Config $gatewayConfig,
        array $data = []
    ) {

        $this->gatewayConfig = $gatewayConfig;

        parent::__construct($context, $taxHelper, $addressConfig, $priceCurrency, $data);
    }

    /**
     * Get shipping method template
     *
     * @return string
     */
    public function getShippingMethodTemplate()
    {
        return 'AuthorizeNet_VisaCheckout::review/shipping_options.phtml';
    }

    /**
     * Get shipping method submit URL
     *
     * @return string
     */
    public function getShippingMethodSubmitUrl()
    {
        return $this->getUrl("anet_visacheckout/checkout/saveShippingMethod", ['_secure' => true]);
    }

    /**
     * Get place order URL on front
     *
     * @return string
     */
    public function getPlaceOrderUrl()
    {
        return $this->getUrl('anet_visacheckout/checkout/place', ['_secure' => true]);
    }
    
    /**
     * Check telephone is required or not
     *
     * @return boolean
     */
    public function isTelephoneRequired()
    {
        return $this->gatewayConfig->isTelephoneRequired();
    }
    
    /**
     * Check billing address form is visible or not
     *
     * @return boolean
     */
    public function isBillingAddressFormVisible()
    {
        return $this->isTelephoneRequired();
    }
    
    /**
     * Check shipping address form is visible or not
     *
     * @return boolean
     */
    public function isShippingAddressFormVisible()
    {
        return $this->isTelephoneRequired();
    }

    /**
     * Get VC button image URL
     *
     * @return string
     */
    public function getVcButtonImageUrl()
    {
        return $this->gatewayConfig->isTestMode()
            ? \AuthorizeNet\VisaCheckout\Block\Button::SANDBOX_BUTTON_URL
            : \AuthorizeNet\VisaCheckout\Block\Button::LIVE_BUTTON_URL;
    }
    
    /**
     * Get call id
     *
     * @return string
     */
    public function getCallId()
    {
        return $this->getQuote()->getPayment()->getAdditionalInformation(\AuthorizeNet\VisaCheckout\Model\Checkout::PARAM_CALL_ID);
    }
    
    /**
     * Get VC API key
     *
     * @return string
     */
    public function getVcApiKey()
    {
        return $this->gatewayConfig->getApiKey();
    }
    
    /**
     * Get VC button config
     *
     * @return array
     */
    public function getVcButtonConfig()
    {
        return [
            'callId' => $this->getCallId(),
            'apiKey' => $this->getVcApiKey(),
        ];
    }
}
