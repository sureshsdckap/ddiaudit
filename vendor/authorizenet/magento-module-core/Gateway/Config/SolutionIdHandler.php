<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

class SolutionIdHandler implements \Magento\Payment\Gateway\Config\ValueHandlerInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * SolutionIdHandler Constructor
     *
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Retrieve a method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->config->getSolutionId();
    }
}
