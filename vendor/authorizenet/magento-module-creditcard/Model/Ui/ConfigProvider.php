<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Model\Ui;

use AuthorizeNet\CreditCard\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get config data
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
                    'availableCardTypes' => $this->config->getAvailableCardTypes(),
                    'centinelActive' => $this->config->isCentinelActive(),
                    'vaultCode' => Config::VAULT_CODE,
                    'loginId' => $this->config->getLoginId(),
                    'clientKey' => $this->config->getClientKey()
                ]
            ]
        ];
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode()
    {
        return Config::CODE;
    }
}
