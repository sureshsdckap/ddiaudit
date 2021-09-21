<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Controller\Cca;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use AuthorizeNet\Centinel\Model\Config;
use Magento\Checkout\Model\Session;

class GetToken extends Action
{
    const JWT_EXP_TIME = 3600;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \Lcobucci\JWT\BuilderFactory
     */
    private $builderFactory;

    /**
     * @var \Magento\Framework\Math\Random
     */
    private $random;

    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256
     */
    private $sha256;

    /**
     * GetToken constructor
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Config $config
     * @param BuilderFactory $builderFactory
     * @param Sha256 $sha256
     * @param Random $random
     * @param Session $session
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Config $config,
        \Lcobucci\JWT\BuilderFactory $builderFactory,
        \Lcobucci\JWT\Signer\Hmac\Sha256 $sha256,
        \Magento\Framework\Math\Random $random,
        Session $session
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->config = $config;
        $this->session = $session;
        $this->builderFactory = $builderFactory;
        $this->random = $random;
        $this->sha256 = $sha256;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $quote = $this->session->getQuote();

            $payload = [
                'OrderDetails' => [
                    'OrderNumber' => $this->random->getUniqueHash('order_'), // we don't know order id at this moment
                    'Amount' => round($quote->getBaseGrandTotal() * 100),
                    'CurrencyCode' => $quote->getBaseCurrencyCode()
                ]
            ];

            $token = $this->generateToken($payload);
            $result->setData(['status' => true, 'jwt' => $token->__toString()]);
        } catch (\Exception $e) {
            $result->setData(['status' => false, 'error' => $e->getMessage()]);
            $result->setHttpResponseCode(400);
        }

        return $result;
    }

    /**
     * Generate Payload token.
     *
     * @param array $payload
     * @return \Lcobucci\JWT\Token
     */
    private function generateToken($payload)
    {
        $tokenBuilder = $this->builderFactory->create();

        $jwtId = $this->random->getUniqueHash('jwt_');
        $currentTime = $this->getTime();

        $jwt = $tokenBuilder
            ->setId($jwtId, true)
            ->setIssuer($this->config->getApiId())
            ->setIssuedAt($currentTime)
            ->setExpiration($currentTime + self::JWT_EXP_TIME)
            ->set('OrgUnitId', $this->config->getUnitId())
            ->set('Payload', $payload)
            ->set('ObjectifyPayload', true)
            ->sign(
                $this->sha256,
                $this->config->getApiKey()
            )->getToken();

        return $jwt;
    }

    /**
     *  Get current time.
     *
     * @return time()
     * @codeCoverageIgnoreStart
     */
    protected function getTime()
    {
        return time();
    }
    // @codeCoverageIgnoreEnd
}
