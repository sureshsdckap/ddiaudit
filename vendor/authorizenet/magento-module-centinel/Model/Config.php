<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const KEY_API_ID = 'fraud_protection/centinel/api_id';
    const KEY_UNIT_ID = 'fraud_protection/centinel/unit_id';
    const KEY_API_KEY = 'fraud_protection/centinel/api_key';
    const KEY_TEST_MODE = 'fraud_protection/centinel/test_mode';

    const CENTINEL_CCA_ACTION_SUCCESS = 'SUCCESS';
    const CENTINEL_CCA_ACTION_NOACTION = 'NOACTION';
    const CENTINEL_CCA_ACTION_FAILURE = 'FAILURE';
    const CENTINEL_CCA_ACTION_ERROR = 'ERROR';

    const CENTINEL_ACTIVE_CONFIG_KEY = 'centinel_active';
    const CENTINEL_CCA_DATA_SESSION_INDEX = 'centinel_cca_data';

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config contractor
     *
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param Encryptor $encryptor
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        Encryptor $encryptor
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * Get Centinel configuration data.
     *
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId ?: $this->storeManager->getStore()->getId()
        );
    }

    /**
     * To check Test Mode is Enabled or Not
     *
     * @return bool
     */
    public function isTestMode()
    {
        return $this->getConfigData(self::KEY_TEST_MODE);
    }

    /**
     * Get centinel api id
     *
     * @return string
     */
    public function getApiId()
    {
        return $this->getConfigData(self::KEY_API_ID);
    }

    /**
     * Get centinel unit id
     *
     * @return string
     */
    public function getUnitId()
    {
        return $this->getConfigData(self::KEY_UNIT_ID);
    }

    /**
     * Get centinel Api key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->encryptor->decrypt(
            $this->getConfigData(self::KEY_API_KEY)
        );
    }
}
