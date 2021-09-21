<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Config;

/**
 * @codeCoverageIgnore
 */
class Config extends \AuthorizeNet\Core\Gateway\Config\Config implements \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface
{
    const CODE = 'anet_paypal_express';

    const KEY_PAYMENT_ACTION = 'payment_action';

    /**
     * Get payment action
     *
     * @return string
     */
    public function getPaymentAction()
    {
        return $this->getConfigValue(self::KEY_PAYMENT_ACTION);
    }

    /**
     * Check for button enable or not on a product page
     *
     * @return bool
     */
    public function isButtonEnabledOnProduct()
    {
        return true; //TODO: create option and change to actual config reading
    }

    /**
     * Check for button enable or not on a cart page
     *
     * @return bool
     */
    public function isButtonEnabledOnCart()
    {
        return true; //TODO: create option and change to actual config reading
    }
}
