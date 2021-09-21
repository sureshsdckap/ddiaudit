<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Plugin;

use Magento\Framework\Exception\LocalizedException;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use AuthorizeNet\CreditCard\Gateway\Config\Config;
use AuthorizeNet\Core\Gateway\Request\VaultTransactionRequestBuilder;

use net\authorize\api\contract\v1 as AnetAPI;

class VaultTransactionRequestBuilderPlugin
{
    const KEY_INFO_VAULT_CVV = 'vault_cvv';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var bool
     */
    private $isAdminArea;

    /**
     * VaultTransactionRequestBuilderPlugin Constructor
     *
     * @param SubjectReader $subjectReader
     * @param Config $config
     * @param bool $isAdminArea
     */
    public function __construct(
        SubjectReader $subjectReader,
        Config $config,
        $isAdminArea = false
    ) {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
        $this->isAdminArea = $isAdminArea;
    }

    /**
     * Build request
     *
     * Create the payment request for Anet API.
     *
     * @param VaultTransactionRequestBuilder $subject
     * @param \Closure $proceed
     * @param array $commandSubject
     * @return array
     *
     */
    public function aroundBuild(
        VaultTransactionRequestBuilder $subject,
        \Closure $proceed,
        array $commandSubject
    ) {
        $result = $proceed($commandSubject);

        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $payment = $paymentDO->getPayment();

        if ($payment->getMethodInstance()->getCode() != Config::VAULT_CODE || !$this->getVaultRequireCvv()) {
            return $result;
        }

        if (! $cvv = $payment->getAdditionalInformation(self::KEY_INFO_VAULT_CVV)) {
            throw new LocalizedException(__('CVV is required'));
        }

        /** @var AnetAPI\CreateTransactionRequest $anetRequest */
        $anetRequest = array_shift($result);

        $anetRequest->getTransactionRequest()
            ->getProfile()
            ->getPaymentProfile()
                ->setCardCode($cvv);

        return ['request' => $anetRequest];
    }

    /**
     * Get CVV vault for admin
     *
     * @return bool
     */
    private function getVaultRequireCvv()
    {
        return $this->isAdminArea
            ? $this->config->getVaultAdminRequireCvv()
            : $this->config ->getVaultRequireCvv();
    }
}
