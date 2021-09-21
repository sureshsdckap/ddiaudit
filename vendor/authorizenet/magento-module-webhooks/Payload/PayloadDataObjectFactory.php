<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload;

use Magento\Framework\ObjectManagerInterface;

class PayloadDataObjectFactory
{

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;
    /**
     * @var \AuthorizeNet\Webhooks\Model\TransactionFinder
     */
    private $transactionFinder;

    /**
     * PayloadDataObjectFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \AuthorizeNet\Webhooks\Model\TransactionFinder $transactionFinder
    ) {
        $this->objectManager = $objectManager;
        $this->orderRepository = $orderRepository;
        $this->transactionFinder = $transactionFinder;
    }

    /**
     * Create a Payload using transaction and orders data
     *
     * @param \AuthorizeNet\Webhooks\Api\PayloadInterface $payload
     * @return PayloadDataObject
     */
    public function create(\AuthorizeNet\Webhooks\Api\PayloadInterface $payload)
    {

        $payloadData = $payload->getPayload();

        $transaction = $this->transactionFinder->getTransaction($payloadData['id']);

        $order = null;

        if ($transaction->getId()) {
            $order = $this->orderRepository->get($transaction->getOrderId());
        } else {
            $transaction = null;
        }

        return $this->objectManager->create(PayloadDataObject::class, [
            'payload' => $payload,
            'order' => $order,
            'transaction' => $transaction,
        ]);
    }
}
