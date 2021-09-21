<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

class DeclineFraudHandler implements HandlerInterface
{
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader
     */
    protected $subjectReader;

    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }


    /**
     * Decline the order of Fraud transaction
     *
     * @param array $subject
     * @return mixed|string
     * @throws \Exception
     */
    public function handle(array $subject)
    {
        $payloadDO = $this->subjectReader->readPayload($subject);

        $payload = $payloadDO->getPayload();

        $order = $payloadDO->getOrder();

        if (!$order || !$order->getId()) {
            throw new \Exception(
                __('Unable to find appropriate order for transaction %1', $payload->getPayload()['id'])
            );
        }

        if (!$order->canReviewPayment()) {
            throw new \Exception(
                __('Cannot update order #' . $order->getIncrementId())
            );
        }

        $order->getPayment()->setSkipGatewayCommand(true);
        $order->getPayment()->deny();
        $order->save();
        return __('Order #%1 declined', $order->getIncrementId());
    }
}
