<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Gateway\Config;

use AuthorizeNet\Core\Model\Source\PaymentAction;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Config
 * @codeCoverageIgnore
 */
class Config extends \AuthorizeNet\Core\Gateway\Config\Config implements \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface
{
    const CODE = 'anet_visacheckout';

    const KEY_CCTYPES = "cctypes";
    const KEY_PAYMENT_ACTION = "payment_action";
    const KEY_ALLOWSPECIFIC = "allowspecific";
    const KEY_API_KEY = "api_key";
    const KEY_DISABLE_PHONE_REQUIREMENT = 'disable_telephone_requirement';
    const KEY_ENABLE_VC_PRODUCT_PAGE = 'enable_product_page_button';
    const KEY_ENABLE_VC_CART = 'enable_cart_page_button';

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Encryption\EncryptorInterface
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $storeManager, $encryptor, $methodCode, $pathPattern);
    }

    /**
     * Get CC type
     *
     * @return array
     */
    public function getCCTypes()
    {
        return $this->getValue(self::KEY_CCTYPES);
    }

    /**
     * Get payment action
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION);
    }

    /**
     * Get API key
     *
     * @return array
     */
    public function getApiKey()
    {
        return $this->getValue(self::KEY_API_KEY);
    }

    /**
     * Check for mode
     *
     * @return bool
     */
    public function isAuthMode()
    {
        return $this->getValue(self::KEY_PAYMENT_ACTION) ==
            PaymentAction::ACTION_AUTHORIZE;
    }

    /**
     * Check telephone is required or not
     *
     * @return bool
     */
    public function isTelephoneRequired()
    {
        return !$this->getValue(self::KEY_DISABLE_PHONE_REQUIREMENT);
    }

    /**
     * Check VC checkout button is enable or not on product
     *
     * @return bool
     */
    public function isButtonEnabledOnProduct()
    {
        return $this->getValue(self::KEY_ENABLE_VC_PRODUCT_PAGE);
    }

    /**
     * Check VC checkout button enable or not in cart
     *
     * @return bool
     */
    public function isButtonEnabledOnCart()
    {
        return $this->getValue(self::KEY_ENABLE_VC_CART);
    }
}
