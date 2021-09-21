<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

use Magento\Sales\Model\Order;

class ApproveFraudHandler implements HandlerInterface
{

    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * ApproveFraudHandler constructor.
     *
     * @param \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }


    /**
     * Review the order Payment
     * Skip Gateway Command to accept the payment
     *
     * @param array $subject
     * @return \Magento\Framework\Phrase|mixed
     * @throws \Exception
     */
    public function handle(array $subject)
    {
        $payloadDO = $this->subjectReader->readPayload($subject);

        $payload = $payloadDO->getPayload();

        $order = $payloadDO->getOrder();

        if (!$order) {
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
        $order->getPayment()->accept();
        $order->save();
        return __('Order #%1 approved', $order->getIncrementId());
    }
}
