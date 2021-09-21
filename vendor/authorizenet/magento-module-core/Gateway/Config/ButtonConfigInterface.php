<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

interface ButtonConfigInterface
{

    /**
     * Check Is button enable or not on product page.
     *
     * @return bool
     */
    public function isButtonEnabledOnProduct();

    /**
     *
     * Check Is button enable or not on cart page.
     *
     * @return bool
     */
    public function isButtonEnabledOnCart();
}
