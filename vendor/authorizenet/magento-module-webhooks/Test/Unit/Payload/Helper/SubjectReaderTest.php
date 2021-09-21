<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Payload\Helper;

use AuthorizeNet\Webhooks\Payload\Helper\SubjectReader;
use PHPUnit\Framework\TestCase;

class SubjectReaderTest extends TestCase
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    protected function setUp()
    {
        $this->subjectReader = new SubjectReader();
    }

    public function testReadPayload()
    {
        $this->subjectReader->readPayload(['payload' => 'payload']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Payload doesn't exist
     */
    public function testReadPayloadException()
    {
        $this->subjectReader->readPayload([]);
    }
}
