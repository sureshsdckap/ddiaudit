<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload;

use AuthorizeNet\Webhooks\Api\PayloadInterface;

class Processor implements ProcessorInterface
{
    /**
     * @var PayloadDataObjectFactory
     */
    protected $payloadDataObjectFactory;
    /**
     * @var \AuthorizeNet\Webhooks\Model\ResourceModel\Payload
     */
    protected $payloadResource;
    /**
     * @var Handler\HandlerPoolInterface
     */
    protected $handlerPool;
    /**
     * Processor Constructor
     *
     * @param PayloadDataObjectFactory                           $payloadDataObjectFactoryFactory
     * @param \AuthorizeNet\Webhooks\Model\ResourceModel\Payload $payloadResource
     * @param Handler\HandlerPoolInterface                       $handlerPool
     */
    public function __construct(
        PayloadDataObjectFactory $payloadDataObjectFactoryFactory,
        \AuthorizeNet\Webhooks\Model\ResourceModel\Payload $payloadResource,
        Handler\HandlerPoolInterface $handlerPool
    ) {
        $this->payloadDataObjectFactory = $payloadDataObjectFactoryFactory;
        $this->payloadResource = $payloadResource;
        $this->handlerPool = $handlerPool;
    }

    /**
     * {inheritdoc}
     *
     * @param PayloadInterface|\Magento\Framework\Model\AbstractModel $payload
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function process(PayloadInterface $payload)
    {
        try {
            $handler = $this->handlerPool->get($payload->getEventType());
            $payloadDO = $this->payloadDataObjectFactory->create($payload);
            $details = $handler->handle(['payload' => $payloadDO]);
            $payload->setStatus(PayloadInterface::STATUS_PROCESSED);
        } catch (\Exception $e) {
            $details = $e->getMessage();
            $payload->setStatus(PayloadInterface::STATUS_FAILED);
        }
        $payload->setDetails($details);
        $this->payloadResource->save($payload);
    }
}
