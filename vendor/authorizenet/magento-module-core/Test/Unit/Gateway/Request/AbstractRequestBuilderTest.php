<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use AuthorizeNet\Core\Gateway\Config\Reader;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use PHPUnit\Framework\TestCase;

/**
 * Class AbstractRequestBuilderTest
 * @package AuthorizeNet\Core\Gateway\Request
 */
class AbstractRequestBuilderTest extends TestCase
{
    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var AbstractRequestBuilderTestHelper
     */
    protected $requestBuilder;

    /**
     * @var Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var $transactionType
     */
    protected $transactionType;

    /**
     *
     */
    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(Reader::class)->disableOriginalConstructor()->getMock();
        $this->transactionType = 'authCaptureTransaction';

        $this->requestBuilder = new \AuthorizeNet\Core\Test\Unit\Gateway\Request\AbstractRequestBuilderTestHelper(
            $this->configMock,
            $this->subjectReaderMock,
            $this->transactionType
        );
    }

    public function testProtectedMethods()
    {
        $payment = 'noPaymentClass';
        $this->assertEquals(null, $this->requestBuilder->getTax($payment));
        $this->assertEquals(null, $this->requestBuilder->getShipping($payment));
    }

    public function testPrepareAddressDataWithNull()
    {
        static::assertEquals(null, $this->requestBuilder->___prepareAddressData(null, false));
    }
}
