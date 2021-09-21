<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Block\Checkout;

class Review extends \AuthorizeNet\Core\Block\Checkout\AbstractReview
{

    /**
     * Get shipping method template
     *
     * @return string
     */
    public function getShippingMethodTemplate()
    {
        return 'AuthorizeNet_PayPalExpress::review/shipping_options.phtml';
    }

    /**
     * Get shipping method submit URL
     *
     * @return string
     */
    public function getShippingMethodSubmitUrl()
    {
        return $this->getUrl("anet_paypal_express/checkout/saveShippingMethod", ['_secure' => true]);
    }

    /**
     * Get place order URL
     *
     * @return string
     */
    public function getPlaceOrderUrl()
    {
        return $this->getUrl('anet_paypal_express/checkout/place', ['_secure' => true]);
    }

    /**
     * Get paypal account information
     *
     * @return array
     */
    public function getPayPalAccount()
    {
        return $this->getQuote()->getPayment()->getAdditionalInformation(
            \AuthorizeNet\PayPalExpress\Model\Checkout::KEY_PAYER_EMAIL
        );
    }
}
