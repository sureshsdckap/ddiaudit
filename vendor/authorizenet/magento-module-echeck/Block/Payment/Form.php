<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Block\Payment;

use Magento\Framework\View\Element\Template;

class Form extends \Magento\Payment\Block\Form
{
    /**
     * @var string
     */
    protected $_template = 'AuthorizeNet_ECheck::payment/form.phtml';

    /**
     * @var \AuthorizeNet\ECheck\Gateway\Config\Config
     */
    protected $config;

    /**
     * Form Constructor
     *
     * @param Template\Context $context
     * @param \AuthorizeNet\ECheck\Gateway\Config\Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \AuthorizeNet\ECheck\Gateway\Config\Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Get account type options
     *
     * @return array
     */
    public function getAccountTypeOptions()
    {
        return $this->config->getAccountTypeOptions();
    }
}
