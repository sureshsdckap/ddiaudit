<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Model\Merchant;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Model\Merchant\DataProvider;

class DataProviderTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|MockObject
     */
    protected $gatewayConfigMock;

    /**
     * @var \Magento\Backend\Model\UrlInterface|MockObject
     */
    protected $urlMock;

    /**
     * @var DataProvider
     */
    protected $dataProvider;

    protected function setUp()
    {

        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMockForAbstractClass();
        $this->gatewayConfigMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->urlMock = $this->getMockBuilder(\Magento\Backend\Model\UrlInterface::class)->getMockForAbstractClass();

        $this->dataProvider = new DataProvider(
            'some_name',
            'pk_field',
            'some_field',
            $this->scopeConfigMock,
            $this->gatewayConfigMock,
            $this->urlMock
        );
    }

    /**
     * @param $loginId
     * @param $transKey
     * @param $expectedLoginRequired
     * @param $expectedTransKeyRequired
     * @dataProvider dataProviderTestGetMeta
     */
    public function testGetMeta($loginId, $transKey, $expectedLoginRequired, $expectedTransKeyRequired)
    {

        $this->gatewayConfigMock->expects(static::any())->method('getLoginId')->willReturn($loginId);
        $this->gatewayConfigMock->expects(static::any())->method('getTransKey')->willReturn($transKey);

        $url = 'someUrl';

        $this->urlMock->expects(static::once())->method('getUrl')->with('anet_core/merchant/getDetails')->willReturn($url);

        $meta = $this->dataProvider->getMeta();

        static::assertEquals($expectedLoginRequired, $meta['merchant_keys']['children']['login_id']['arguments']['data']['config']['validation']['required-entry']);
        static::assertEquals($expectedTransKeyRequired, $meta['merchant_keys']['children']['transaction_key']['arguments']['data']['config']['validation']['required-entry']);
        static::assertEquals($url, $meta['merchant_keys']['arguments']['data']['config']['detailsUrl']);
    }

    public function dataProviderTestGetMeta()
    {
        return [
            [
                'loginId' => 'some value',
                'transKey' => 'some value',
                'expectedLoginRequired' => false,
                'expectedTransKeyRequire' => false,
            ],
            [
                'loginId' => '',
                'transKey' => '',
                'expectedLoginRequired' => true,
                'expectedTransKeyRequire' => true,
            ],
        ];
    }

    /**
     * @param $loginId
     * @param $transactionKey
     * @param $expectedLoginId
     * @param $expectedTransKey
     * @dataProvider dataProviderTestGetData
     */
    public function testGetData($loginId, $transactionKey, $expectedLoginId, $expectedTransKey)
    {
        $testMode = true;
        $this->gatewayConfigMock->expects(static::any())->method('getLoginId')->willReturn($loginId);
        $this->gatewayConfigMock->expects(static::any())->method('getTransKey')->willReturn($transactionKey);
        $this->gatewayConfigMock->expects(static::any())->method('isTestMode')->willReturn($testMode);

        $data = $this->dataProvider->getData();

        static::assertEquals($expectedLoginId, $data['']['login_id']);
        static::assertEquals($expectedTransKey, $data['']['transaction_key']);
        static::assertEquals($testMode, $data['']['sandbox_mode']);
    }

    public function dataProviderTestGetData()
    {
        return [
            [
                'loginId' => 'some value',
                'transKey' => 'some value',
                'expectedLoginId' => 'some value',
                'expectedTransKey' => DataProvider::MASKED_VALUE,
            ],
            [
                'loginId' => '',
                'transKey' => '',
                'expectedLoginId' => '',
                'expectedTransKey' => '',
            ],
        ];
    }

    /**
     * @covers \AuthorizeNet\Core\Model\Merchant\DataProvider
     */
    public function testGetConfigData()
    {
        $url = 'someUrl';
        $this->urlMock->expects(static::once())->method('getUrl')->with('anet_core/merchant/save')->willReturn($url);

        $config = $this->dataProvider->getConfigData();

        static::assertEquals($url, $config['submit_url']);
    }
}
