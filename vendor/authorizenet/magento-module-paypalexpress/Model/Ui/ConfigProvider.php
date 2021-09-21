<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Model\Ui;

use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use AuthorizeNet\PayPalExpress\Gateway\Config\Config;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * ConfigProvider Constructor
     *
     * @param Config $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get payment method configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                Config::CODE => [
                    'active' => $this->config->isActive(),
                    'title' => $this->config->getTitle(),
                    'test' => $this->config->isTestMode(),
                    'initActionUrl' => $this->getInitActionUrl()
                ]
            ]
        ];
    }

    /**
     * Initialize action URL
     *
     * @return string
     */
    private function getInitActionUrl()
    {
        return $this->urlBuilder->getDirectUrl('anet_paypal_express/checkout/initialize/');
    }
}
