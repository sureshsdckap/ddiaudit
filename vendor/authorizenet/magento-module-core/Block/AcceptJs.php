<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Block;

use Magento\Framework\View\Element\Template;

class AcceptJs extends Template
{
    const ACCEPT_JS_PROD_URL = 'https://js.authorize.net/v1/Accept.js';
    const ACCEPT_JS_TEST_URL = 'https://jstest.authorize.net/v1/Accept.js';

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config
     */
    protected $config;

    /**
     * AcceptJs Constructor
     *
     * @param Template\Context $context
     * @param \AuthorizeNet\Core\Gateway\Config\Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \AuthorizeNet\Core\Gateway\Config\Config $config,
        array $data = []
    ) {
        if (isset($data['method_code'])) {
            $config->setMethodCode($data['method_code']);
        }

        $this->config = $config;

        parent::__construct($context, $data);
    }

    /**
     * Retrive the URL of Accept.js as per Test Mode
     *
     * @return string
     */
    public function getAcceptJsUrl()
    {
        return $this->config->isTestMode() ? self::ACCEPT_JS_TEST_URL : self::ACCEPT_JS_PROD_URL;
    }
}
