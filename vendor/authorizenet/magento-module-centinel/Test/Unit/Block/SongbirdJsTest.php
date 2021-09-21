<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Test\Unit\Block;

use AuthorizeNet\Centinel\Block\SongbirdJs;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit\Framework\TestCase;

class SongbirdJsTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Centinel\Model\Config|MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var SongbirdJs
     */
    protected $block;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Centinel\Model\Config::class)->disableOriginalConstructor()->getMock();
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)->disableOriginalConstructor()->getMock();

        $this->block = new SongbirdJs(
            $this->contextMock,
            $this->configMock
        );
    }

    /**
     * @dataProvider dataProviderTestGetSongbirdJsUrl
     */
    public function testGetSongbirdJsUrl($testMode, $expectedUrl)
    {
        $this->configMock->expects(static::any())->method('isTestMode')->willReturn($testMode);

        static::assertEquals($expectedUrl, $this->block->getSongbirdJsUrl());
    }

    public function dataProviderTestGetSongbirdJsUrl()
    {
        return [
            ['testMode' => true, 'expectedUrl' => \AuthorizeNet\Centinel\Block\SongbirdJs::SONGBIRD_JS_TEST_URL],
            ['testMode' => false, 'expectedUrl' => \AuthorizeNet\Centinel\Block\SongbirdJs::SONGBIRD_JS_PROD_URL],
        ];
    }
}
