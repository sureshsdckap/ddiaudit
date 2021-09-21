<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Block\Payment;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use AuthorizeNet\CreditCard\Gateway\Config\Config as GatewayConfig;

/**
 * Class Form
 * @codeCoverageIgnore
 * @package AuthorizeNet\CreditCard\Block\Payment
 */
class Form extends \Magento\Payment\Block\Form\Cc
{
    /**
     * @var $_template
     */
    protected $_template = 'AuthorizeNet_CreditCard::form/cc.phtml';

    /**
     * @var $gatewayConfig
     */
    protected $gatewayConfig;

    /**
     * Form Constructor
     *
     * @param Context $context
     * @param Config $paymentConfig
     * @param GatewayConfig $gatewayConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        GatewayConfig $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->gatewayConfig = $gatewayConfig;
    }
}
