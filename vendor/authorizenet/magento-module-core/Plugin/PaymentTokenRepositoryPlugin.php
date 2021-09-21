<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Plugin;

use AuthorizeNet\Core\Gateway\Config\Config;
use Magento\Vault\Model\PaymentTokenRepository;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface;

class PaymentTokenRepositoryPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * PaymentTokenRepositoryPlugin Constructor
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Set valid tokes ot items
     *
     * @param PaymentTokenRepository $subject
     * @param PaymentTokenSearchResultsInterface $result
     *
     * @return mixed
     */
    public function afterGetList(PaymentTokenRepository $subject, $result)
    {
        $validTokens = [];
        foreach ($result->getItems() as $token) {
            if ($this->isValidToken($token)) {
                $validTokens[] = $token;
            }
        }

        return $result->setItems($validTokens);
    }

    /**
     * To Check the token is valid or not
     *
     * @param PaymentTokenInterface $token
     * @return bool
     */
    private function isValidToken($token)
    {
        $gatewayTokenParts = explode(':', $token->getGatewayToken());
        if (strpos($token->getPaymentMethodCode(), 'anet_') === 0 && isset($gatewayTokenParts[2])) {
            return $this->config->getLoginId() == $gatewayTokenParts[2];
        }

        return true;
    }
}
