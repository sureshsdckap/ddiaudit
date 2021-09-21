<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Model\Ui;

use AuthorizeNet\ECheck\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigProvider Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieve the the payment method configuration data
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                $this->getCode() => [
                    'active' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'agreementTemplate' => $this->config->getAgreementTemplate(),
                    'accountTypeOptions' => $this->config->getAccountTypeOptions(),
                    'vaultCode' => Config::VAULT_CODE,
                    'loginId' => $this->config->getLoginId(),
                    'clientKey' => $this->config->getClientKey(),
                ]
            ]
        ];
    }

    /**
     * Get config code
     *
     * @return string
     */
    public function getCode()
    {
        return Config::CODE;
    }
}
