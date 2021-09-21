<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model;

use AuthorizeNet\Webhooks\Api\TransactionInterface;

class Transaction implements TransactionInterface
{
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    protected $request;
    /**
     * @var \AuthorizeNet\Core\Model\Logger
     */
    protected $logger;
    /**
     * @var \AuthorizeNet\Webhooks\Model\payloadFactory
     */
    protected $payloadFactory;
    /**
     * @var Config
     */
    protected $config;

    /**
     * Transaction constructor
     *
     * @param \Magento\Framework\Webapi\Rest\Request $request
     * @param \AuthorizeNet\Core\Model\Logger $logger
     * @param \AuthorizeNet\Webhooks\Model\payloadFactory $payloadFactory
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\Webapi\Rest\Request $request,
        \AuthorizeNet\Core\Model\Logger $logger,
        \AuthorizeNet\Webhooks\Model\PayloadFactory $payloadFactory,
        Config $config
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->payloadFactory = $payloadFactory;
        $this->config = $config;
    }

    /**
     * @api
     * @param string $notificationId
     * @param string $eventType
     * @param string $eventDate
     * @param string $webhookId
     * @param mixed $payload
     * @return void
     */
    public function transaction($notificationId, $eventType, $eventDate, $webhookId, $payload)
    {
        if (!$this->verifySignature()) {
            return;
        }

        $payloadModel = $this->payloadFactory->create();
        $payloadModel->setData([
            'notification_id' => $notificationId,
            'event_type' => $eventType,
            'event_date' => $eventDate,
            'webhook_id' => $webhookId,
            'payload' => json_encode($payload),
            'status' => \AuthorizeNet\Webhooks\Api\PayloadInterface::STATUS_PENDING
        ]);
        $payloadModel->save();
    }

    /**
     * Verify the request header signature
     *
     * @return bool
     */
    protected function verifySignature()
    {
        $signature = $this->request->getHeader('X-ANET-Signature');
        $data = $this->request->getContent();
        $key = $this->config->getSignatureKey();
        $hash = strtoupper(hash_hmac('sha512', $data, $key));

        if ($hash == substr($signature, 7)) {
            $this->logger->info($data);
            return true;
        }

        $this->logger->warning('Wrong Signature: ' . $data);

        return false;
    }
}
