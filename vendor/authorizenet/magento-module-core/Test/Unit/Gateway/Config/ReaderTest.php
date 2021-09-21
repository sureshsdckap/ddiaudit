<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Config;

use AuthorizeNet\Core\Gateway\Config\Config;
use PHPUnit\Framework\TestCase;
use \AuthorizeNet\Core\Gateway\Config\Reader;

class ReaderTest extends TestCase
{
    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodMock;

    /**
     * @var Reader
     */
    protected $reader;

    protected function setUp()
    {

        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();
        $this->reader = new Reader();
    }

    public function testGetLoginId()
    {

        $expectedValue = '123123';

        $this->methodMock
            ->expects(static::once())
            ->method('getConfigData')
            ->with(Config::KEY_LOGIN_ID)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->reader->getLoginId($this->methodMock));
    }

    public function testGetTransactionKey()
    {
        $expectedValue = '123123';

        $this->methodMock
            ->expects(static::once())
            ->method('getConfigData')
            ->with(Config::KEY_TRANS_KEY)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->reader->getTransactionKey($this->methodMock));
    }

    public function testGetSolutionId()
    {
        $expectedValue = '123123';

        $this->methodMock
            ->expects(static::once())
            ->method('getConfigData')
            ->with(Config::KEY_SOLUTION_ID)
            ->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->reader->getSolutionId($this->methodMock));
    }
}
