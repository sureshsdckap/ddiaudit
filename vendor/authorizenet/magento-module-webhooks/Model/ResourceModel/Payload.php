<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Payload
 * @package AuthorizeNet\Webhooks\Model\ResourceModel
 * @codeCoverageIgnore
 */
class Payload extends AbstractDb
{
    /**
     * Payload Constructor
     */
    protected function _construct()
    {
        $this->_init('anet_webhooks_payload', 'payload_id');
    }
}
