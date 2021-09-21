<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Plugin;

class ConfiguratorPlugin
{

    /**
     * @var \AuthorizeNet\Core\Model\CcTypes
     */
    protected $ccTypes;

    /**
     * ConfiguratorPlugin Constructor
     *
     * @param \AuthorizeNet\Core\Model\CcTypes $ccTypes
     */
    public function __construct(
        \AuthorizeNet\Core\Model\CcTypes $ccTypes
    ) {
        $this->ccTypes = $ccTypes;
    }

    /**
     * Get section data
     *
     * Prepare payment method section of enabled CC type
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

        $result['data.cc_types_text'][] = __('In this step you could enable credit cart payments for your store and select accepted credit card types.');
        $result['data.cc_enabled'] = false;
        $result['data.cc_types'] = [];

        if (empty($details['paymentMethods'])) {
            $result['data.cc_types_text'] = implode(' ', $result['data.cc_types_text']);
            return $result;
        }

        foreach ($details['paymentMethods'] as $method) {
            if (!in_array($method, $this->ccTypes->getAvailableAuthorizeNetTypes())) {
                continue;
            }

            $result['data.cc_types'][] = $this->ccTypes->getMagentoType($method);
        }

        if (!empty($result['data.cc_types'])) {
            $result['data.cc_enabled'] = true;
            $result['data.cc_types_text'][] = __('We have selected all credit cards that your merchant account supports. Please review that list.');
        }

        $result['data.cc_types_text'] = implode(' ', $result['data.cc_types_text']);

        return $result;
    }

    /**
     * Get config path map
     *
     * Merge enabled CC types with the result.
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
            'cc_enabled' => 'payment/anet_creditcard/active',
            'cc_types' => 'payment/anet_creditcard/cctypes'
        ]);
    }
}
