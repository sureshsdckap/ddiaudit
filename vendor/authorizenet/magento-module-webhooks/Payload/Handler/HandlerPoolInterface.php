<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Handler;

/**
 * Interface HandlerPoolInterface
 * @package AuthorizeNet\Webhooks\Payload\Handler
 * @codeCoverageIgnore
 */
interface HandlerPoolInterface
{

    /**
     * Retrieves operation
     *
     * @param string $type
     * @throws \Magento\Framework\Exception\NotFoundException
     * @return HandlerInterface
     */
    public function get($type);
}
