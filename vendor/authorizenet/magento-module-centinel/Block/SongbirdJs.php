<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Block;

use AuthorizeNet\Centinel\Model\Config;
use Magento\Framework\View\Element\Template;

class SongbirdJs extends Template
{
    const SONGBIRD_JS_PROD_URL = 'https://includes.ccdc02.com/cardinalcruise/v1/songbird.js';
    const SONGBIRD_JS_TEST_URL = 'https://includestest.ccdc02.com/cardinalcruise/v1/songbird.js';

    /**
     * @var Config
     */
    protected $config;

    /**
     * SongbirdJs constructor
     *
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Confirm the Test mode to set the songbird.js URL
     *
     * @return string
     */
    public function getSongbirdJsUrl()
    {
        return $this->config->isTestMode() ? self::SONGBIRD_JS_TEST_URL : self::SONGBIRD_JS_PROD_URL;
    }
}
