<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model\Merchant;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    const MASKED_VALUE = '*******';

    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config
     */
    private $config;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \AuthorizeNet\Core\Gateway\Config\Config $config,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get meta
     *
     * @return array
     */
    public function getMeta()
    {

        $this->meta['merchant_keys']['children']['login_id']['arguments']['data']['config']['validation']['required-entry'] = !(bool)$this->config->getLoginId();
        $this->meta['merchant_keys']['children']['transaction_key']['arguments']['data']['config']['validation']['required-entry'] = !(bool)$this->config->getTransKey();
        $this->meta['merchant_keys']['arguments']['data']['config']['detailsUrl'] = $this->urlBuilder->getUrl('anet_core/merchant/getDetails');


        return $this->meta;
    }

    /**
     * Add Filter
     *
     * @param \Magento\Framework\Api\Filter
     * @return void
     * @codeCoverageIgnore
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        return [
            '' => [
                'login_id' => $this->config->getLoginId(),
                'transaction_key' => $this->config->getTransKey() ? self::MASKED_VALUE : '',
                'sandbox_mode' => $this->config->isTestMode(),
            ],
        ];
    }

    /**
     * Get Config Data
     *
     * @return array
     */
    public function getConfigData()
    {
        return array_replace_recursive(
            parent::getConfigData(),
            [
                'submit_url' => $this->urlBuilder->getUrl('anet_core/merchant/save'),
            ]
        );
    }
}
