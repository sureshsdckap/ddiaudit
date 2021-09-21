<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Payload\Handler;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Framework\ObjectManager\TMap;

class HandlerPool implements HandlerPoolInterface
{

    /**
     * @var HandlerInterface[] | TMap
     */
    private $commands;

    /**
     * HandlerPool Constructor
     *
     * @param TMapFactory $tmapFactory
     * @param array $handlers
     */
    public function __construct(
        TMapFactory $tmapFactory,
        array $handlers = []
    ) {
        $this->commands = $tmapFactory->create(
            [
                'array' => $handlers,
                'type' => HandlerInterface::class
            ]
        );
    }

    /**
     * Retrieves operation
     *
     * @param string $type
     * @return HandlerInterface
     * @throws NotFoundException
     */
    public function get($type)
    {
        if (!isset($this->commands[$type])) {
            throw new NotFoundException(__('Handler for type %1 does not exist.', $type));
        }

        return $this->commands[$type];
    }
}
