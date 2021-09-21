<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Model\Cca;

use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use AuthorizeNet\Centinel\Model\Config;
use Magento\Framework\Exception\LocalizedException;

class Validator
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Sha256
     */
    protected $signer;

    /**
     * Validator contractor
     *
     * @param Config $config
     * @param Sha256 $signer
     */
    public function __construct(
        Config $config,
        Sha256 $signer
    ) {
        $this->config = $config;
        $this->signer = $signer;
    }

    /**
     *  Check for token validation.
     *
     *  Check token expired or not if expired then throw exception message
     *  Check token validate or not if not then throw exception message
     *  Check payload atda valide or not if not then throw exception message
     *  Check if 3DSecure is in strict mode to validate extended data
     *
     * @param JWT\Token $token
     * @throws LocalizedException
     * @throws \Exception
     */
    public function validate($token)
    {
        $payload = $token->getClaim('Payload');

        if ($token->isExpired()) {
            throw new LocalizedException(__('JWT is expired'));
        }

        if (! $token->verify($this->signer, $this->config->getApiKey())) {
            throw new LocalizedException(__('JWT signature verification failed'));
        }

        if (! $payload->Validated) {
            throw new LocalizedException(__('CCA validation failed'));
        }

        // @TODO check if 3DSecure is in strict mode to validate extended data

        switch ($payload->ActionCode) {
            case Config::CENTINEL_CCA_ACTION_SUCCESS:
            case Config::CENTINEL_CCA_ACTION_NOACTION:
                break;

            case Config::CENTINEL_CCA_ACTION_FAILURE:
            case Config::CENTINEL_CCA_ACTION_ERROR:
                throw new \Exception('CCA failed: ' . $payload->ErrorDescription);
            default:
                throw new LocalizedException(__('CCA failed: unknown action code'));
        }
    }
}
