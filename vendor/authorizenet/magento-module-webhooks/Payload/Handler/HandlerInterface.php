<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 * @codeCoverageIgnore
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

/**
 * Interface HandlerInterface
 * @package AuthorizeNet\Webhooks\Payload\Handler
 * @codeCoverageIgnore
 */
interface HandlerInterface
{

    /**
     * Handler Interface of payload
     *
     * @param array $subject
     * @return mixed
     */
    public function handle(array $subject);
}
