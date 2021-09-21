<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Block;

use Magento\Framework\View\Element\Template;

class Button extends \Magento\Framework\View\Element\Template implements \Magento\Catalog\Block\ShortcutInterface
{
    const SANDBOX_BUTTON_URL = "https://sandbox.secure.checkout.visa.com/wallet-services-web/xo/button.png";
    const LIVE_BUTTON_URL = "https://secure.checkout.visa.com/wallet-services-web/xo/button.png";

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config
     */
    protected $config;
    
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Button Constructor
     *
     * @param \AuthorizeNet\VisaCheckout\Gateway\Config\Config $config
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \AuthorizeNet\VisaCheckout\Gateway\Config\Config $config,
        \Magento\Framework\Math\Random $mathRandom,
        Template\Context $context,
        array $data = []
    ) {
        $this->mathRandom = $mathRandom;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Get shortcut alias
     *
     * @return string
     */
    public function getAlias()
    {
        return 'product.info.addtocart.visa_checkout';
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
     * Generate HTML
     *
     * @return html|null
     */
    protected function _beforeToHtml()
    {

        $this->setShortcutHtmlId(
            $this->mathRandom->getUniqueHash('vc_button_')
        );
        
        return parent::_beforeToHtml();
    }

    /**
     * Check Is active or not
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->config->isActive();
    }
    
    /**
     * Get button image URL
     *
     * @return array
     */
    public function getButtonImageUrl()
    {
        return $this->config->isTestMode()
            ? self::SANDBOX_BUTTON_URL
            : self::LIVE_BUTTON_URL;
    }
    
    /**
     * Get API key
     *
     * @return array
     */
    public function getApiKey()
    {
        return $this->config->getApiKey();
    }
}
