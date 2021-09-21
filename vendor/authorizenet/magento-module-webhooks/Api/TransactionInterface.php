<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Api;

/**
 * Interface TransactionInterface
 * @package AuthorizeNet\Webhooks\Api
 * @codeCoverageIgnore
 */
interface TransactionInterface
{
    /**
     * Transaction new object instance
     *
     * @api
     * @param string $notificationId
     * @param string $eventType
     * @param string $eventDate
     * @param string $webhookId
     * @param mixed $payload
     * @return void
     */
    public function transaction($notificationId, $eventType, $eventDate, $webhookId, $payload);
}
