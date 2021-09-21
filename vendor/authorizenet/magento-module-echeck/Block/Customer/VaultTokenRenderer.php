<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Block\Customer;

use AuthorizeNet\ECheck\Gateway\Config\Config;
use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractTokenRenderer;

class VaultTokenRenderer extends AbstractTokenRenderer
{
    const ECHECK_MASK = 'XXXX';

    /**
     * @var Config
     */
    private $config;

    /**
     * VaultTokenRenderer Constructor
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
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token)
    {
        return $token->getPaymentMethodCode() === Config::CODE;
    }

    /**
     * Get routing number
     *
     * @return string
     */
    public function getRoutingNumber()
    {
        return self::ECHECK_MASK . '-' . $this->getTokenDetails()['routingNumber'];
    }

    /**
     * Get account number
     *
     * @return string
     */
    public function getAccountNumber()
    {
        return self::ECHECK_MASK . '-' . $this->getTokenDetails()['accountNumber'];
    }

    /**
     * Get account name
     *
     * @return string
     */
    public function getAccountName()
    {
        return $this->getTokenDetails()['accountName'];
    }

    /**
     * Get account type
     *
     * @return string
     */
    public function getAccountType()
    {
        return $this->getTokenDetails()['accountType'];
    }

    /**
     * @inheritdoc
     */
    public function getIconUrl()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getIconHeight()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getIconWidth()
    {
        return '';
    }
}
