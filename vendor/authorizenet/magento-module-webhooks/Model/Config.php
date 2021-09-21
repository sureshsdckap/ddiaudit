<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model;

class Config extends \AuthorizeNet\Core\Gateway\Config\Config
{
    const CODE = 'anet_webhooks';
    const SENDER = 'sender';
    const RECIPIENT_EMAIL = 'email';
    const EMAIL_TEMPLATE = 'email_template';

    /**
     * Get configured recipient email
     *
     * @return string
     */
    public function getRecipientEmail()
    {
        return $this->getCoreConfigValue(self::RECIPIENT_EMAIL, self::CODE);
    }

    /**
     * Get configured sender email
     *
     * @return string
     */
    public function getSender()
    {
        return $this->getCoreConfigValue(self::SENDER, self::CODE);
    }

    /**
     * Get configured email template
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->getCoreConfigValue(self::EMAIL_TEMPLATE, self::CODE);
    }
}
