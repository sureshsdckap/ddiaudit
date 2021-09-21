<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Plugin;

class ConfiguratorPlugin
{

    const PAYMENT_METHOD = 'Paypal';

    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Config\Config
     */
    protected $config;

    /**
     * ConfiguratorPlugin Constructor
     *
     * @param \AuthorizeNet\PayPalExpress\Gateway\Config\Config $config
     */
    public function __construct(\AuthorizeNet\PayPalExpress\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get PayPal Express Section data
     *
     * @param  \AuthorizeNet\Core\Model\Merchant\Configurator $subject
     * @param  callable $proceed
     * @param  array $details
     * @return array $result
     */
    public function aroundGetSectionsData(
        \AuthorizeNet\Core\Model\Merchant\Configurator $subject,
        callable $proceed,
        $details
    ) {

        $result = $proceed($details);

        $result['data.paypal_express_enabled'] = in_array(self::PAYMENT_METHOD, $details['paymentMethods']);

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
            'paypal_express_enabled' => 'payment/anet_paypal_express/active'
        ]);
    }
}
