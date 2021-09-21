<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Command;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\TransactionRepositoryInterface;
use Magento\Sales\Api\Data\TransactionInterface;

class CaptureStrategyCommand implements \Magento\Payment\Gateway\CommandInterface
{
    const AUTHORIZE_CAPTURE = 'sale';
    const PRIOR_AUTH_CAPTURE = 'settle';

    /**
     * @var CommandPoolInterface
     */
    protected $commandPool;

    /**
     * @var TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * CaptureStrategyCommand Constructor
     *
     * @param CommandPoolInterface $commandPool
     * @param TransactionRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        TransactionRepositoryInterface $repository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SubjectReader $subjectReader
    ) {
        $this->commandPool = $commandPool;
        $this->transactionRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function execute(array $commandSubject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->subjectReader->readPayment($commandSubject)->getPayment();

        $command = $this->_getCommand($payment);
        $this->commandPool->get($command)->execute($commandSubject);
    }

    /**
     * Capture the transaction
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return string
     * @throws \Exception
     */
    protected function _getCommand(\Magento\Sales\Model\Order\Payment $payment)
    {
        if ($this->_captureTransactionExists($payment)) {
            throw new \Exception('transaction was already captured');
        }

        if (!$payment->getAuthorizationTransaction()) {
            return self::AUTHORIZE_CAPTURE;
        }
        
        return self::PRIOR_AUTH_CAPTURE;
    }

    /**
     * To check capture transaction exists or not
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    protected function _captureTransactionExists(\Magento\Sales\Model\Order\Payment $payment)
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('payment_id')
                    ->setValue($payment->getId())
                    ->create(),
            ]
        );

        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('txn_type')
                    ->setValue(TransactionInterface::TYPE_CAPTURE)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();

        return (bool)$this->transactionRepository->getList($searchCriteria)->getTotalCount();
    }
}
