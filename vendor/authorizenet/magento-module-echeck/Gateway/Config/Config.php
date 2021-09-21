<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Gateway\Config;

use AuthorizeNet\ECheck\Model\Source\AccountType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @codeCoverageIgnore
 */
class Config extends \AuthorizeNet\Core\Gateway\Config\Config
{
    const CODE = 'anet_echeck';
    const VAULT_CODE = 'anet_echeck_vault';
    
    const KEY_AGREEMENT_TEMPLATE = 'agreement_template';

    /**
     * @var AccountType
     */
    protected $accountType;

    /**
     * Config Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param AccountType $accountType
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        AccountType $accountType,
        $methodCode = self::CODE,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        $this->accountType = $accountType;

        parent::__construct(
            $scopeConfig,
            $storeManager,
            $encryptor,
            $methodCode,
            $pathPattern
        );
    }

    /**
     * Get agreement template
     *
     * @return string
     */
    public function getAgreementTemplate()
    {
        return $this->getValue(self::KEY_AGREEMENT_TEMPLATE);
    }

    /**
     * Get account type options
     *
     * Insert the element to account type options.
     *
     * @return array
     */
    public function getAccountTypeOptions()
    {
        $_accountTypes = $this->accountType->toOptionArray();
        array_unshift($_accountTypes, ['value' => '', 'label' => __('--Please Select--')]);

        return $_accountTypes;
    }
}
