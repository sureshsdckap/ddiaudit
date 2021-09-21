<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload;

class PayloadDataObject implements PayloadDataObjectInterface
{

    const KEY_PAYLOAD = 'payload';
    const KEY_ORDER = 'order';
    const KEY_TRANSACTION = 'transaction';

    /**
     * @var \AuthorizeNet\Webhooks\Api\PayloadInterface
     */

    private $payload;
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */

    private $order;

    /**
     * @var \Magento\Sales\Api\Data\TransactionInterface
     */
    private $transaction;

    /**
     * PayloadDataObject Constructor
     *
     * @param \AuthorizeNet\Webhooks\Api\PayloadInterface       $payload
     * @param \Magento\Sales\Api\Data\TransactionInterface|null $transaction
     * @param \Magento\Sales\Api\Data\OrderInterface|null       $order
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Api\PayloadInterface $payload,
        \Magento\Sales\Api\Data\TransactionInterface $transaction = null,
        \Magento\Sales\Api\Data\OrderInterface $order = null
    ) {
    
        $this->payload = $payload;
        $this->order = $order;
        $this->transaction = $transaction;
    }

    /**
     * @inheritdoc
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @inheritdoc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @inheritdoc
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
