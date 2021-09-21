<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Plugin;

class ConfiguratorPlugin
{

    const PAYMENT_METHOD = 'Echeck';

    /**
     * @var \AuthorizeNet\PayPalExpress\Gateway\Config\Config
     */
    protected $config;

    /**
     * ConfiguratorPlugin Constructor
     *
     * @param \AuthorizeNet\ECheck\Gateway\Config\Config $config
     */
    public function __construct(\AuthorizeNet\ECheck\Gateway\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Get Section data
     *
     * Prepare the payment method section of enabled payment methods
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

        $result['data.anet_echeck_enabled'] = in_array(self::PAYMENT_METHOD, $details['paymentMethods']);

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
            'anet_echeck_enabled' => 'payment/anet_echeck/active'
        ]);
    }
}
