<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Cron;

use AuthorizeNet\Webhooks\Api\PayloadInterface;

class Queue
{
    /**
     * @var \AuthorizeNet\Webhooks\Model\ResourceModel\Payload\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \AuthorizeNet\Webhooks\Payload\ProcessorInterface
     */
    protected $payloadProcessor;

    /**
     * Queue constructor.
     * @param \AuthorizeNet\Webhooks\Model\ResourceModel\Payload\CollectionFactory $collectionFactory
     * @param \AuthorizeNet\Webhooks\Payload\ProcessorInterface $payloadProcessor
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Model\ResourceModel\Payload\CollectionFactory $collectionFactory,
        \AuthorizeNet\Webhooks\Payload\ProcessorInterface $payloadProcessor
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->payloadProcessor = $payloadProcessor;
    }

    /**
     * Retrieve all the pending process collection of Webhooks.
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('status', PayloadInterface::STATUS_PENDING);
        foreach ($collection as $item) {
            /* @var \AuthorizeNet\Webhooks\Model\Payload $item */
            $this->payloadProcessor->process($item);
        }
    }
}
