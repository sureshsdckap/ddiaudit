<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Model\ResourceModel\Payload;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package AuthorizeNet\Webhooks\Model\ResourceModel\Payload
 * @codeCoverageIgnore
 */
class Collection extends AbstractCollection
{
    /**
     * Collection Constructor
     *
     * @param \AuthorizeNet\Webhooks\Model\Payload
     * @param \AuthorizeNet\Webhooks\Model\ResourceModel\Payload
     */
    protected function _construct()
    {
        $this->_init(
            \AuthorizeNet\Webhooks\Model\Payload::class,
            \AuthorizeNet\Webhooks\Model\ResourceModel\Payload::class
        );
    }
}
