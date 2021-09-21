<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model\Method;

use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;

class Vault extends \Magento\Vault\Model\Method\Vault
{
    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    private $vaultProvider;

    /**
     * @var \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface
     */
    private $valueHandlerPool;

    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * Vault Constructor
     *
     * @param \Magento\Payment\Gateway\ConfigInterface $config
     * @param \Magento\Payment\Gateway\ConfigFactoryInterface $configFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Payment\Model\MethodInterface $vaultProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool
     * @param \Magento\Payment\Gateway\Command\CommandManagerPoolInterface $commandManagerPool
     * @param \Magento\Vault\Api\PaymentTokenManagementInterface $tokenManagement
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param string $code
     */
    public function __construct(
        \Magento\Payment\Gateway\ConfigInterface $config,
        \Magento\Payment\Gateway\ConfigFactoryInterface $configFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Payment\Model\MethodInterface $vaultProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface $valueHandlerPool,
        \Magento\Payment\Gateway\Command\CommandManagerPoolInterface $commandManagerPool,
        \Magento\Vault\Api\PaymentTokenManagementInterface $tokenManagement,
        \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        string $code
    ) {

        $this->vaultProvider = $vaultProvider;
        $this->valueHandlerPool = $valueHandlerPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;

        // @codeCoverageIgnoreStart
        parent::__construct(
            $config,
            $configFactory,
            $objectManager,
            $vaultProvider,
            $eventManager,
            $valueHandlerPool,
            $commandManagerPool,
            $tokenManagement,
            $paymentExtensionFactory,
            $code
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return (bool)$this->getConfiguredValue('can_fetch_transaction_info');
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        return $this->vaultProvider->fetchTransactionInfo($payment, $transactionId);
    }

    /**
     * Get configured value
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    private function getConfiguredValue($field, $storeId = null)
    {
        $handler = $this->valueHandlerPool->get($field);

        $subject = ['field' => $field];

        if ($this->getInfoInstance()) {
            $subject['payment'] = $this->paymentDataObjectFactory->create($this->getInfoInstance());
        }

        return $handler->handle($subject, $storeId ?: $this->getStore());
    }
}
