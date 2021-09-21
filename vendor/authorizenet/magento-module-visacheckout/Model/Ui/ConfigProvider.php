<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use AuthorizeNet\VisaCheckout\Gateway\Config\Config;
use Magento\Framework\Locale\ResolverInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'anet_visacheckout';

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var Config
     */
    private $config;

    /**
     * ConfigProvider Constructor
     *
     * @param Config $config
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        Config $config,
        ResolverInterface $localeResolver
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $isVisaCheckoutActive = $this->config->isActive();
        return [
            'payment' => [
                self::CODE => [
                    'isActive' => $isVisaCheckoutActive,
                    'title' => $this->config->getTitle(),
                    'api_key' => $this->config->getApiKey(),
                ]
            ]
        ];
    }
}
