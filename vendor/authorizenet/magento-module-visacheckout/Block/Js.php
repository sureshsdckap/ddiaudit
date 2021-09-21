<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Block;

use Magento\Framework\View\Element\Template;

class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var $config
     */
    protected $config;

    /**
     * Js Constructor
     *
     * @param Template\Context $context
     * @param \AuthorizeNet\VisaCheckout\Gateway\Config\Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \AuthorizeNet\VisaCheckout\Gateway\Config\Config $config,
        array $data = []
    ) {
    
        $this->config = $config;
        
        parent::__construct($context, $data);
    }
    
    /**
     * Check Is sandbox enable or not
     *
     * @return boolean
     */
    public function isSandbox()
    {
        return $this->config->isTestMode();
    }
}
