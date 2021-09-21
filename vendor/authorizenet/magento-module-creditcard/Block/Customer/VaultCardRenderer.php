<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Block\Customer;

use AuthorizeNet\CreditCard\Gateway\Config\Config;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\CcConfigProvider;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;

class VaultCardRenderer extends AbstractCardRenderer
{
    const CARD_MASK = 'XXXX';

    /**
     * @var Config
     */
    private $config;

    /**
     * VaultCardRenderer Constructor
     *
     * @param Template\Context $context
     * @param CcConfigProvider $iconsProvider
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CcConfigProvider $iconsProvider,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $iconsProvider, $data);
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
     * Get last 4 digit of credit card
     *
     * @return string
     * @since 100.1.0
     */
    public function getNumberLast4Digits()
    {
        return self::CARD_MASK . '-' . substr($this->getTokenDetails()['cardNumber'], -4);
    }

    /**
     * Get expiry date of credit card
     *
     * @return string
     * @since 100.1.0
     */
    public function getExpDate()
    {
        return $this->getTokenDetails()['cardExpMonth'] . '/' . $this->getTokenDetails()['cardExpYear'];
    }

    /**
     * Get card type icon URL
     *
     * @return string
     * @since 100.1.0
     */
    public function getIconUrl()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['url'];
    }

    /**
     * Get card type icon height
     *
     * @return int
     * @since 100.1.0
     */
    public function getIconHeight()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['height'];
    }

    /**
     * Get card type icon width
     *
     * @return int
     * @since 100.1.0
     */
    public function getIconWidth()
    {
        return $this->getIconForType($this->getTokenDetails()['cardType'])['width'];
    }
}
