<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload;

/**
 * Interface ProcessorInterface
 * @package AuthorizeNet\Webhooks\Payload
 * @codeCoverageIgnore
 */
interface ProcessorInterface
{

    /**
     * Process new object instance
     *
     * @param $payload
     * @return void
     */
    public function process(\AuthorizeNet\Webhooks\Api\PayloadInterface $payload);
}
