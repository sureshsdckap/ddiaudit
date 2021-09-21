<?php

namespace AuthorizeNet\Core\Test\Unit\Block\Form\Element\Link;

use AuthorizeNet\Core\Block\Form\Element\Link\Configuration;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator
     */
    protected $block;

    public function testGetElementHtml()
    {
        $factoryElementMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\Factory::class)->disableOriginalConstructor()->getMock();
        $factoryCollectionMock = $this->getMockBuilder(\Magento\Framework\Data\Form\Element\CollectionFactory::class)->disableOriginalConstructor()->getMock();
        $escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)->disableOriginalConstructor()->getMock();
        $urlBuilderMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)->disableOriginalConstructor()->getMock();

        $urlParam = 'anet_core/merchant/setup';
        $url = 'https://localhost/admin/' . $urlParam;
        $html = '<button class="primary" type="button" onclick="window.open(\'' . $url . '\',\'_self\')"><span>' . __("Run Authorize.Net Configuration Wizard") . '</span></button>';

        $urlBuilderMock->expects(static::once())
            ->method('getUrl')
            ->with($urlParam)
            ->willReturn($url);

        $this->block = new Configuration(
            $factoryElementMock,
            $factoryCollectionMock,
            $escaperMock,
            $urlBuilderMock,
            []
        );

        static::assertEquals($html, $this->block->getElementHtml());
    }
}
