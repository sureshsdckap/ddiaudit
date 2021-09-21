<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Api;

/**
 * Interface PayloadInterface
 * @package AuthorizeNet\Webhooks\Api
 * @codeCoverageIgnore
 */
interface PayloadInterface
{
    const PAYLOAD_ID = 'payload_id';
    const NOTIFICATION_ID = 'notification_id';
    const EVENT_TYPE = 'event_type';
    const EVENT_DATE = 'event_date';
    const WEBHOOK_ID = 'webhook_id';
    const PAYLOAD = 'payload';
    const DETAILS = 'details';
    const STATUS = 'status';

    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSED = 'processed';
    const STATUS_FAILED = 'failed';

    /**
     * Get Payload id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get Payload notification id
     *
     * @return string
     */
    public function getNotificationId();

    /**
     * Get Payload event type
     *
     * @return string
     */
    public function getEventType();

    /**
     * Get Payload event Data
     *
     * @return string
     */
    public function getEventDate();

    /**
     * Get Payload webhook id
     *
     * @return string
     */
    public function getWebhookId();

    /**
     * Get Payload payload
     *
     * @return array
     */
    public function getPayload();

    /**
     * Get Payload details
     *
     * @return string
     */
    public function getDetails();

    /**
     * Get Payload status
     *
     * @return integer
     */
    public function getStatus();

    /**
     * Set Payload id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id);

    /**
     * Set Payload notification id
     *
     * @param string $id
     * @return $this
     */
    public function setNotificationId($id);

    /**
     * Set Payload event type
     *
     * @param string $type
     * @return $this
     */
    public function setEventType($type);

    /**
     * Set Payload event Date
     *
     * @param string $date
     * @return $this
     */
    public function setEventDate($date);

    /**
     * Set Payload webhook id
     *
     * @param string $id
     * @return $this
     */
    public function setWebhookId($id);

    /**
     * Set payload
     *
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload);

    /**
     * Set Payload details
     *
     * @param string $details
     * @return $this
     */
    public function setDetails($details);

    /**
     * Set Payload status
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status);
}
