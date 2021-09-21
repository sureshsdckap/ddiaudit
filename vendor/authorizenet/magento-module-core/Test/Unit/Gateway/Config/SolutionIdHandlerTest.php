<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Config;

use AuthorizeNet\Core\Gateway\Config\Config;
use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Gateway\Config\SolutionIdHandler;

class SolutionIdHandlerTest extends TestCase
{
    /**
     * @var  Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var SolutionIdHandler
     */
    protected $handler;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $this->handler = new SolutionIdHandler(
            $this->configMock
        );
    }

    /**
     * @param $testMode
     * @param $solutionId
     * @dataProvider dataProviderTestHandle
     */
    public function testHandle($testMode, $solutionId)
    {
        $this->configMock->method('getSolutionId')->willReturn($solutionId);
        static::assertEquals($solutionId, $this->handler->handle([]));
    }

    public function dataProviderTestHandle()
    {
        return [
            [
                'testMode' => true,
                'solutionId' => Config::TEST_SOLUTION_ID
            ],
            [
                'testMode' => false,
                'solutionId' => Config::PROD_SOLUTION_ID
            ],
        ];
    }
}
