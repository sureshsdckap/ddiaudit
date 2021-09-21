<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model;

use AuthorizeNet\Webhooks\Api\PayloadInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Payload
 * @package AuthorizeNet\Webhooks\Model
 * @codeCoverageIgnore
 */
class Payload extends AbstractModel implements PayloadInterface
{

    /**
     * Payload Constructor
     */
    protected function _construct()
    {
        $this->_init(\AuthorizeNet\Webhooks\Model\ResourceModel\Payload::class);
    }

    /**
     * Get Payload id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getData(self::PAYLOAD_ID);
    }

    /**
     * Get Payload notification id
     *
     * @return string
     */
    public function getNotificationId()
    {
        return $this->getData(self::NOTIFICATION_ID);
    }

    /**
     * Get Payload event type
     *
     * @return string
     */
    public function getEventType()
    {
        return $this->getData(self::EVENT_TYPE);
    }

    /**
     * Get event Data
     *
     * @return string
     */
    public function getEventDate()
    {
        return $this->getData(self::EVENT_DATE);
    }

    /**
     * Get Payload webhook id
     *
     * @return string
     */
    public function getWebhookId()
    {
        return $this->getData(self::WEBHOOK_ID);
    }

    /**
     * Get Payload data
     *
     * @return array
     */
    public function getPayload()
    {
        return json_decode($this->getData(self::PAYLOAD), true);
    }

    /**
     * Get Payload details
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->getData(self::DETAILS);
    }

    /**
     * Get Payload status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Payload id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::PAYLOAD_ID, $id);
    }

    /**
     * Set Payloadsnotification id
     *
     * @param string $id
     * @return $this
     */
    public function setNotificationId($id)
    {
        return $this->setData(self::NOTIFICATION_ID, $id);
    }

    /**
     * Set Payload event type
     *
     * @param string $type
     * @return $this
     */
    public function setEventType($type)
    {
        return $this->setData(self::EVENT_TYPE, $type);
    }

    /**
     * Set Payload event Data
     *
     * @param string $date
     * @return $this
     */
    public function setEventDate($date)
    {
        return $this->setData(self::EVENT_TYPE, $date);
    }

    /**
     * Set Payload webhook id
     *
     * @param string $id
     * @return $this
     */
    public function setWebhookId($id)
    {
        return $this->setData(self::WEBHOOK_ID, $id);
    }

    /**
     * Set Payload
     *
     * @param string $payload
     * @return $this
     */
    public function setPayload($payload)
    {
        return $this->setData(self::PAYLOAD, $payload);
    }

    /**
     * Set Payload details
     *
     * @param string $details
     * @return $this
     */
    public function setDetails($details)
    {
        return $this->setData(self::DETAILS, $details);
    }

    /**
     * Set Payload status
     *
     * @param integer $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
