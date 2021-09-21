<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Plugin;

class ConfiguratorPlugin
{

    const PAYMENT_METHOD_VISACHECKOUT = 'VisaCheckout';

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config
     */
    protected $config;

    /**
     * ConfiguratorPlugin Constructor
     *
     * @param \AuthorizeNet\VisaCheckout\Gateway\Config\Config $config
     */
    public function __construct(\AuthorizeNet\VisaCheckout\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get Sections Data
     *
     * @param  \AuthorizeNet\Core\Model\Merchant\Configurator $subject
     * @param  callable                                       $proceed
     * @param  array                                          $details
     * @return array                                          $result
     */
    public function aroundGetSectionsData(
        \AuthorizeNet\Core\Model\Merchant\Configurator $subject,
        callable $proceed,
        $details
    ) {

        $result = $proceed($details);

        $result['data.visa_checkout_text'][] =__('Visa checkout is a quick and secure PCI DSS compliant way of paying orders implemented by Visa. ' .
            'In this step you could enable it and configure your API key for it.');
        $result['data.visa_checkout_enabled'] = false;
        $result['data.visa_checkout_api_key'] = '';

        if (!in_array(self::PAYMENT_METHOD_VISACHECKOUT, $details['paymentMethods'])) {
            $result['data.visa_checkout_text'] = implode(' ', $result['data.visa_checkout_text']);
            return $result;
        }

        $result['data.visa_checkout_enabled'] = true;

        if ($apiKey = $this->config->getApiKey()) {
            $result['data.visa_checkout_api_key'] = $apiKey;
            $result['data.visa_checkout_text'][] = __('We see that you have Visa Checkout enabled for your account and you have already configured your API key. Please review it and change if necessary.');
        } else {
            $result['data.visa_checkout_text'][] = __('We see that you have Visa Checkout enabled for your account. Please enter your Visa Checkout API key in a field above.');
        }

        $result['data.visa_checkout_text'] = implode(' ', $result['data.visa_checkout_text']);

        return $result;
    }

    /**
     * Get config path map
     *
     * @param  \AuthorizeNet\Core\Model\Merchant\Configurator $subject
     * @param  array $result
     * @return array
     */
    public function afterGetConfigPathMap(
        \AuthorizeNet\Core\Model\Merchant\Configurator $subject,
        $result
    ) {
        return array_merge($result, [
            'visa_checkout_enabled' => 'payment/anet_visacheckout/active',
            'visa_checkout_api_key' => 'payment/anet_visacheckout/api_key'
        ]);
    }

    /**
     * Get encrypted fields
     *
     * @param  \AuthorizeNet\Core\Model\Merchant\Configurator $subject
     * @param  array                                         $result
     * @return array
     */
    public function afterGetEncryptedFields(
        \AuthorizeNet\Core\Model\Merchant\Configurator $subject,
        $result
    ) {
        return array_merge($result, ['visa_checkout_api_key']);
    }
}
