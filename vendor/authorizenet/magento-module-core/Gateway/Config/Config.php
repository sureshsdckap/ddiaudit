<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const CODE = 'anet_core';

    const CORE_PATH_PATTERN = 'authorize_net/%s/%s';

    const PROD_SOLUTION_ID = 'AAA172611';
    const TEST_SOLUTION_ID = 'AAA100302';

    const KEY_ACTIVE = "active";
    const KEY_TITLE = "title";
    const KEY_DEBUG = "debug";
    const KEY_TEST_MODE = 'test_mode';
    const KEY_LOGIN_ID = "login_id";
    const KEY_TRANS_KEY = "trans_key";
    const KEY_CLIENT_KEY = "client_key";
    const KEY_SIGNATURE_KEY = "signature_key";
    const KEY_SPECIFICCOUNTRY = "specificcountry";
    const KEY_SOLUTION_ID = 'solution_id';

    const TRANS_SUFFIX_CAPTURE = "-capture";
    const TRANS_SUFFIX_VOID = "-void";

    /**
     * @var $methodCode
     */
    protected $methodCode;

    /**
     * @var $pathPattern
     */
    protected $pathPattern;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var bool
     */
    protected $sandboxMode;

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface  $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface    $encryptor
     * @param string                $methodCode
     * @param string                $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->methodCode = $methodCode;
        $this->pathPattern = $pathPattern;

        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Set Payment method code
     *
     * @param string $methodCode
     * @return Config
     */
    public function setMethodCode($methodCode)
    {
        $this->methodCode = $methodCode;
        return $this;
    }

    /**
     * To Check the Payment method active or not
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool)$this->getConfigValue(self::KEY_ACTIVE);
    }

    /**
     * Get Authorize.Net login id
     *
     * @return string
     */
    public function getLoginId()
    {
        return $this->getCoreConfigValue(self::KEY_LOGIN_ID, self::CODE);
    }

    /**
     * Get authorize.net transaction Key
     *
     * @return string
     */
    public function getTransKey()
    {
        return $this->getCoreConfigValue(self::KEY_TRANS_KEY, self::CODE);
    }

    /**
     * Get Authorize.Net Client Key
     *
     * @return string
     */
    public function getClientKey()
    {
        return $this->getCoreConfigValue(self::KEY_CLIENT_KEY, self::CODE);
    }

    /**
     * Get Payment method Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getConfigValue(self::KEY_TITLE);
    }

    /**
     * Get Specific Country to enable payment method for specific country
     *
     * @return mixed
     */
    public function getSpecificCountry()
    {
        return $this->getConfigValue(self::KEY_SPECIFICCOUNTRY);
    }

    /**
     * Check Payment method Debug mode
     *
     * @return bool
     */
    public function isDebugOn()
    {
        return (bool)$this->getConfigValue(self::KEY_DEBUG, self::CODE);
    }

    /**
     * Check Payment method test mode.
     *
     * @return bool
     */
    public function isTestMode()
    {
        if ($this->getSandboxMode() === null) {
            return (bool)$this->getConfigValue(self::KEY_TEST_MODE, self::CODE);
        }
        return $this->getSandboxMode();
    }

    /**
     * Get Solution Id as per the configured Test mode
     *
     * @return string
     */
    public function getSolutionId()
    {
        return $this->isTestMode() ? self::TEST_SOLUTION_ID : self::PROD_SOLUTION_ID;
    }

    /**
     * Get Authorize.Net Signature Key.
     *
     * @return string
     */
    public function getSignatureKey()
    {
        return $this->getCoreConfigValue(self::KEY_SIGNATURE_KEY, self::CODE);
    }

    /**
     * Retrieve the Configuration Value as per selected store
     *
     * @param string $field
     * @param bool $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $code = false, $storeId = null)
    {
        $code = $code ?: $this->methodCode;
        $storeId = $storeId ?: $this->storeManager->getStore()->getId();

        return $this->scopeConfig->getValue(
            sprintf($this->pathPattern, $code, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     *  Retrieve the core Configuration Value
     *
     * This method executes to get config value by field, store and storeid
     *
     * @param  $field
     * @param  $code
     * @param  $storeId
     * @return $value
     */
    protected function getCoreConfigValue($field, $code = false, $storeId = null)
    {
        $originalPathPattern = $this->pathPattern;

        $this->pathPattern = self::CORE_PATH_PATTERN;

        $value = $this->getConfigValue($field, $code, $storeId);

        $this->pathPattern = $originalPathPattern;

        return $value;
    }

    /**
     * Set Sandbox Mode
     *
     * This method executes to set a sandbox mode.
     *
     * @param  bool $value
     * @return string
     */
    public function setSandboxMode($value)
    {
        $this->sandboxMode = $value;
        return $this;
    }

    /**
     * Get Sandbox Mode
     *
     * This method executes to get a sandbox mode.
     *
     * @return string
     */
    public function getSandboxMode()
    {
        return $this->sandboxMode;
    }
}
