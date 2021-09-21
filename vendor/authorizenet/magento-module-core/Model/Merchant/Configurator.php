<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model\Merchant;

class Configurator
{

    const CONFIG_PATH_MAP = [
        'login_id' => 'authorize_net/anet_core/login_id',
        'transaction_key' => 'authorize_net/anet_core/trans_key',
        'signature_key' => 'authorize_net/anet_core/signature_key',
        'client_key' => 'authorize_net/anet_core/client_key',
        'sandbox_mode' => 'payment/anet_core/test_mode'
    ];

    const ENCRYPTED_FIELDS = ['transaction_key', 'signature_key'];

    /**
     * @var $getDetailsCommand
     */
    protected $getDetailsCommand;
    
    /**
     * @var $storeManager
     */
    protected $storeManager;
    
    /**
     * @var $configWriter
     */
    protected $configWriter;
    
    /**
     * @var $encryptor
     */
    protected $encryptor;

    /**
     * Configurator Constructor
     *
     * @param \Magento\Payment\Gateway\CommandInterface             $command
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Framework\Encryption\EncryptorInterface      $encryptor
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Payment\Gateway\CommandInterface $command,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->getDetailsCommand = $command;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->configWriter = $configWriter;
    }

    /**
     * Get Sections Data
     *
     * Get details from login id and transaction key and return section data.
     *
     * @param $loginId
     * @param $transactionKey
     * @return array
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function loadConfig($loginId, $transactionKey)
    {

        $details = $this->getDetailsCommand->execute(['loginId' => $loginId, 'transactionKey' => $transactionKey]);

        return $this->getSectionsData($details);
    }

    /**
     * Process for base currency
     *
     * @param  array $details
     * @return string
     */
    public function processBaseCurrency($details)
    {

        if ($details['currencies'][0] !== $this->storeManager->getStore()->getCurrentCurrency()->getCode()) {
            return [
                'data.base_currency_text' => __('Your base currency does not match you merchant account currency. ' .
                    'Please change your store base currency code to merchant one or contact Authorize.Net support to change your merchant account currency.')
            ];
        }

        return ['data.base_currency_text' => __('Great! Your Base Currency matches you merchant currency. Please follow the next step.')];
    }

    /**
     * Update Client Key
     *
     * @param  array $details
     * @return array $result
     */
    public function processClientKey($details)
    {

        $result = [];

        $result['data.client_key_text'][] = __('This step allows to configure Accept.js client key.');

        if ($details['clientKey']) {
            $result['data.client_key'] = $details['clientKey'];
            $result['data.client_key_text'][] = __('We have fetched your accept.js public client key via API. Please verify it.');
        }

        $result['data.client_key_text'] = implode(' ', $result['data.client_key_text']);

        return $result;
    }

    /**
     * Get Selection Data
     *
     * @param  array $details
     * @return array $result
     */
    public function getSectionsData($details)
    {

        $result = [];

        $result = array_merge($result, $this->processBaseCurrency($details));
        $result = array_merge($result, $this->processClientKey($details));

        return $result;
    }

    /**
     * Get Config Path Map
     *
     * @return count
     */
    public function getConfigPathMap()
    {
        return self::CONFIG_PATH_MAP;
    }

    /**
     * Get Encrypted Fields
     *
     * @return count
     */
    public function getEncryptedFields()
    {
        return self::ENCRYPTED_FIELDS;
    }

    /**
     * Save Config data
     *
     * @param  array $params
     * @param  integer $storeId
     */
    public function saveConfig(array $params, $storeId = 0)
    {
        $configPathMap = $this->getConfigPathMap();
        foreach ($params as $key => $value) {
            if (!isset($configPathMap[$key])) {
                continue;
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            if ($value == 'true') {
                $value = 1;
            }

            if ($value == 'false') {
                $value = 0;
            }

            if (filter_var($value, FILTER_VALIDATE_BOOLEAN)) {
                settype($value, 'int');
            }

            if (in_array($key, $this->getEncryptedFields())) {
                if ($value == '*******') {
                    continue;
                }
                $value = $this->encryptor->encrypt($value);
            }

            $this->saveConfigValue($configPathMap[$key], $value, $storeId);
        }
    }

    /**
     * Save config value
     *
     * @param  string $path
     * @param  string $value
     * @param  integer $storeId
     */
    public function saveConfigValue($path, $value, $storeId = 0)
    {
        $this->configWriter->save(
            $path,
            $value,
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $storeId
        );
    }
}
