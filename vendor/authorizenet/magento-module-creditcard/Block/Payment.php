<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Block;

use AuthorizeNet\CreditCard\Model\Ui\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Payment extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * Payment Constructor
     *
     * @param Context $context
     * @param ConfigProvider $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        ConfigProvider $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $config;
    }

    /**
     * Get encoded data of payment gateway config
     *
     * @return string
     */
    public function getPaymentConfig()
    {
        $payment = $this->configProvider->getConfig()['payment'];
        $config = $payment[$this->getCode()];
        $config['code'] = $this->getCode();
        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    /**
     * Retrieve payment method code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->configProvider->getCode();
    }
}
