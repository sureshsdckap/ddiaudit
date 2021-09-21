<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload;

/**
 * Interface PayloadDataObjectInterface
 * @package AuthorizeNet\Webhooks\Payload
 * @codeCoverageIgnore
 */
interface PayloadDataObjectInterface
{

    /**
     * Get Payload data
     *
     * @return \AuthorizeNet\Webhooks\Api\PayloadInterface
     */
    public function getPayload();

    /**
     * Get Order data
     *
     * @return \Magento\Sales\Model\Order|null
     */
    public function getOrder();

    /**
     * Get Transaction data
     *
     * @return \Magento\Sales\Model\Order\Payment\Transaction|null
     */
    public function getTransaction();
}
