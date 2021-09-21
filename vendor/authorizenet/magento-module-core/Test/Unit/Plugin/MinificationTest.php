<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Plugin;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

use AuthorizeNet\Core\Plugin\Minification;

class MinificationTest extends TestCase
{

    protected $additionalExcludes = ['some', 'another'];

    /**
     * @var Minification
     */
    protected $minificationPlugin;

    /**
     * @var \Magento\Framework\View\Asset\Minification|MockObject
     */
    protected $minification;

    protected function setUp()
    {

        $this->minification = $this->getMockBuilder(\Magento\Framework\View\Asset\Minification::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->minificationPlugin = new Minification(
            $this->additionalExcludes
        );
    }

    /**
     * @param $contentType
     * @param $originalExcludes
     * @param $expectedExcludes
     * @dataProvider dataProviderTestAroundGetExcludes
     */
    public function testAroundGetExcludes($contentType, $originalExcludes, $expectedExcludes)
    {

        $proceeed = function ($contentType) use ($originalExcludes) {
            return $originalExcludes;
        };

        static::assertEquals(
            $expectedExcludes,
            $this->minificationPlugin->aroundGetExcludes($this->minification, $proceeed, $contentType)
        );
    }

    public function dataProviderTestAroundGetExcludes()
    {
        return [
            [
                'contentType' => 'js',
                'originalExcludes' => ['orig1', 'orig2'],
                'expectedExcludes' => array_merge(['orig1', 'orig2'], $this->additionalExcludes)
            ],
            [
                'contentType' => 'not_js',
                'originalExcludes' => ['orig1', 'orig2'],
                'expectedExcludes' => ['orig1', 'orig2'],
            ],
        ];
    }
}
