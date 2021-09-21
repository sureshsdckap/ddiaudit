<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */
namespace AuthorizeNet\PayPalExpress\Block;

class Button extends \Magento\Framework\View\Element\Template implements \Magento\Catalog\Block\ShortcutInterface
{

    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Button Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $Context
     * @param \AuthorizeNet\PayPalExpress\Gateway\Config\Config $Config
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \AuthorizeNet\PayPalExpress\Gateway\Config\Config $config,
        \Magento\Framework\Math\Random $mathRandom,
        array $data = []
    ) {

        $this->config = $config;
        $this->mathRandom = $mathRandom;

        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->isActive()) {
            return parent::_toHtml();
        }

        return '';
    }

    /**
     * Generate Hash for the PayPal Button
     *
     * @return null|HtML
     */
    protected function _beforeToHtml()
    {

        $this->setShortcutHtmlId(
            $this->mathRandom->getUniqueHash('anet_pp_button_')
        );

        return parent::_beforeToHtml();
    }

    /**
     * @inheritdoc
     */
    public function getAlias()
    {
        return 'product.info.addtocart.authorizenet_paypal_checkout';
    }

    /**
     * Check and return condition of enable or not
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->config->isActive();
    }

    /**
     * Check test mode enable or not
     *
     * @return boolean
     */
    public function isTestMode()
    {
        return $this->config->isTestMode();
    }

    /**
     * Initialize the Anet PayPal Express action URL
     *
     * @return string
     */
    public function getInitActionUrl()
    {
        return $this->_urlBuilder->getUrl('anet_paypal_express/checkout/initialize');
    }

    /**
     * Get URL of saveToken route
     *
     * @return string
     */
    public function getSaveTokenActionUrl()
    {
        return $this->_urlBuilder->getUrl('anet_paypal_express/checkout/saveToken');
    }

    /**
     * Get checkout page review URL
     *
     * @return string
     */
    public function getReviewUrl()
    {
        return $this->_urlBuilder->getUrl('anet_paypal_express/checkout/review');
    }

    /**
     * Get PayPal express button label
     *
     * @return string
     */
    public function getButtonLabel()
    {
        return $this->getData('button_label');
    }

    /**
     * Retrieve the configuration of PayPal express payment method
     *
     * @return array
     */
    public function getJsonConfig()
    {
        $config = [
            'blockContainerSelector' => '#' . $this->getShortcutHtmlId(),
            'isCatalogProduct' => (bool)$this->getIsCatalogProduct(),
            'isSandbox' => $this->isTestMode(),
            'initActionUrl' => $this->getInitActionUrl(),
            'reviewUrl' => $this->getReviewUrl(),
            'saveTokenUrl' => $this->getSaveTokenActionUrl(),
            'ignoreShippingAddress' => 1,
        ];

        if ($label = $this->getButtonLabel()) {
            $config['buttonLabel'] = $label;
        }

        return json_encode($config);
    }
}
