<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Model\Ui;

use AuthorizeNet\CreditCard\Gateway\Config\Config;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterface;
use Magento\Vault\Model\Ui\TokenUiComponentProviderInterface;
use Magento\Vault\Model\Ui\TokenUiComponentInterfaceFactory;

class TokenUiComponentProvider implements TokenUiComponentProviderInterface
{
    /**
     * @var TokenUiComponentInterfaceFactory
     */
    private $componentFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * TokenUiComponentProvider Constructor
     *
     * @param TokenUiComponentInterfaceFactory $componentFactory
     * @param Config                           $config
     */
    public function __construct(
        TokenUiComponentInterfaceFactory $componentFactory,
        Config $config
    ) {
        $this->componentFactory = $componentFactory;
        $this->config = $config;
    }

    /**
     * Get UI component for token
     *
     * Generate component for a token from config data and return component.
     *
     * @param PaymentTokenInterface $paymentToken
     * @return TokenUiComponentInterface
     */
    public function getComponentForToken(PaymentTokenInterface $paymentToken)
    {
        $jsonDetails = json_decode($paymentToken->getTokenDetails() ?: '{}', true);
        $component = $this->componentFactory->create(
            [
                'config' => [
                    'code' => Config::VAULT_CODE,
                    'vaultRequreCvv' => $this->config->getVaultRequireCvv(),
                    TokenUiComponentProviderInterface::COMPONENT_DETAILS => $jsonDetails,
                    TokenUiComponentProviderInterface::COMPONENT_PUBLIC_HASH => $paymentToken->getPublicHash()
                ],
                'name' => 'AuthorizeNet_CreditCard/js/view/payment/method-renderer/vault'
            ]
        );

        return $component;
    }
}
