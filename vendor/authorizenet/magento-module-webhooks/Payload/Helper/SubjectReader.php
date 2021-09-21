<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Payload\Helper;

class SubjectReader
{

    /**
     * Get Payload data object Interface
     *
     * @param $subject
     * @return \AuthorizeNet\Webhooks\Payload\PayloadDataObjectInterface
     */
    public function readPayload($subject)
    {
        if (!isset($subject['payload']) || !$subject['payload']) {
            throw new \InvalidArgumentException('Payload doesn\'t exist');
        }
        return $subject['payload'];
    }
}
