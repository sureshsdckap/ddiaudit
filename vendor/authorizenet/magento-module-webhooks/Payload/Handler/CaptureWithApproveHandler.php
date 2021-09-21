<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

class CaptureWithApproveHandler implements HandlerInterface
{


    /**
     * @var HandlerPoolInterface
     */
    protected $handlerPool;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * CaptureStrategyHandler constructor.
     *
     * @param \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
     * @param HandlerPoolInterface $handlerPool
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader,
        \AuthorizeNet\Webhooks\Payload\Handler\HandlerPoolInterface $handlerPool
    ) {
        $this->subjectReader = $subjectReader;
        $this->handlerPool = $handlerPool;
    }

    /**
     * Review the Order payment and capture it
     *
     * @param array $subject
     * @return mixed
     */
    public function handle(array $subject)
    {
        $payload = $this->subjectReader->readPayload($subject);
        $order = $payload->getOrder();

        $results = [];

        if (!$order || !$order->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Order doesn\'t exist.'));
        }

        if ($order->canReviewPayment()) {
            $results[] = $this->handlerPool->get('net.authorize.payment.fraud.approved')->handle($subject);
        }

        $results[] = $this->handlerPool->get('capture_only')->handle($subject);

        return implode(PHP_EOL, $results);
    }
}
