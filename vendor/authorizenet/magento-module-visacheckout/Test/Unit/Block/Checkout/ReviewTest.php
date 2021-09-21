<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Block\Checkout;

use PHPUnit\Framework\TestCase;

class ReviewTest extends TestCase
{

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Tax\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $taxHelper;

    /**
     * @var \Magento\Customer\Model\Address\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressConfigMock;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $currencyMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;
    /**
     * @var Review
     */
    protected $reviewBlock;

    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\View\Element\Template\Context::class)->disableOriginalConstructor()->getMock();
        $this->taxHelper = $this->getMockBuilder(\Magento\Tax\Helper\Data::class)->disableOriginalConstructor()->getMock();
        $this->addressConfigMock = $this->getMockBuilder(\Magento\Customer\Model\Address\Config::class)->disableOriginalConstructor()->getMock();
        $this->currencyMock = $this->getMockBuilder(\Magento\Framework\Pricing\PriceCurrencyInterface::class)->getMockForAbstractClass();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();
        $this->scopeConfigMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)->getMockForAbstractClass();
        $this->urlBuilderMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();
        $this->escaperMock = $this->getMockBuilder(\Magento\Framework\Escaper::class)->disableOriginalConstructor()->getMock();

        $this->contextMock->expects(static::any())
            ->method('getScopeConfig')
            ->willReturn($this->scopeConfigMock);

        $this->contextMock->expects(static::any())
            ->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);
        
        $this->contextMock->expects(static::any())
            ->method('getEscaper')
            ->willReturn($this->escaperMock);

        $this->reviewBlock = new Review(
            $this->contextMock,
            $this->taxHelper,
            $this->addressConfigMock,
            $this->currencyMock,
            $this->configMock,
            []
        );

        $this->reviewBlock->setQuote($this->quoteMock);
    }

    protected function prepareQuoteMockForWithMethods($methods)
    {

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->setMethods($methods)
            ->disableOriginalConstructor()
            ->getMock();

        $this->reviewBlock->setQuote($this->quoteMock);
    }

    public function testSetGetQuote()
    {

        $this->reviewBlock = new Review(
            $this->contextMock,
            $this->taxHelper,
            $this->addressConfigMock,
            $this->currencyMock,
            $this->configMock,
            []
        );

        $this->reviewBlock->setQuote($this->quoteMock);
        static::assertEquals($this->quoteMock, $this->reviewBlock->getQuote());
    }

    /**
     * @param $isVirtual
     * @param $address
     * @dataProvider dataProviderTestGetShippingAddress
     */
    public function testGetShippingAddress($isVirtual, $address)
    {

        $this->quoteMock->expects(static::once())
            ->method('getIsVirtual')
            ->willReturn($isVirtual);

        $this->quoteMock->expects(static::any())
            ->method('getShippingAddress')
            ->willReturn($address);

        static::assertEquals($address, $this->reviewBlock->getShippingAddress());
    }

    public function dataProviderTestGetShippingAddress()
    {
        return [
            ['isVirtual' => false, 'someaddress'],
            ['isVirtual' => true, false],
        ];
    }

    public function testGetBillingAddress()
    {
        $billingAddress = 'someAddress';

        $this->quoteMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturn($billingAddress);

        static::assertEquals($billingAddress, $this->reviewBlock->getBillingAddress());
    }

    public function testGetPaymentMethodTitle()
    {

        $this->prepareQuoteMockForWithMethods(['getPayment', 'getMethodInstance', 'getTitle']);

        $expectedTitle = 'My payment method title';

        $this->quoteMock->expects(static::once())
            ->method('getPayment')
            ->willReturnSelf();

        $this->quoteMock->expects(static::once())
            ->method('getMethodInstance')
            ->willReturnSelf();

        $this->quoteMock->expects(static::once())
            ->method('getTitle')
            ->willReturn($expectedTitle);

        static::assertEquals($expectedTitle, $this->reviewBlock->getPaymentMethodTitle());
    }

    public function testGetEmail()
    {

        $this->prepareQuoteMockForWithMethods(['getBillingAddress', 'getEmail']);

        $expectedEmail = 'test@example.org';

        $this->quoteMock->expects(static::once())
            ->method('getBillingAddress')
            ->willReturnSelf();

        $this->quoteMock->expects(static::once())
            ->method('getEmail')
            ->willReturn($expectedEmail);

        static::assertEquals($expectedEmail, $this->reviewBlock->getEmail());
    }


    public function testGetShippingRateGroups()
    {
        $expectedResult = ['somegroup' => ['rate1', 'rate2', 'rate3']];

        $this->prepareQuoteMockForWithMethods(['getShippingAddress', 'getGroupedAllShippingRates']);

        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturnSelf();

        $this->quoteMock->expects(static::once())
            ->method('getGroupedAllShippingRates')
            ->willReturn($expectedResult);

        static::assertEquals($expectedResult, $this->reviewBlock->getShippingRateGroups());
    }

    /**
     * @covers \AuthorizeNet\VisaCheckout\Block\Checkout\Review::getCurrentShippingRate
     */
    public function testGetCurrentShippingRate()
    {

        $currentCode = 'my_code';

        $groups = [
            'mygroup' => [
                $this->getRateMock('someCode1', 1.22),
                $this->getRateMock('someCode2', 1.42),
                $this->getRateMock('someCode3', 1.52),
                $this->getRateMock($currentCode, 1.72),
                $this->getRateMock('someCode5', 1.92),
            ]
        ];

        $this->prepareQuoteMockForWithMethods([
            'getShippingAddress',
            'getGroupedAllShippingRates',
            'getShippingMethod',
        ]);

        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getShippingAddress')
            ->willReturnSelf();

        $this->quoteMock->expects(static::once())
            ->method('getGroupedAllShippingRates')
            ->willReturn($groups);

        $this->quoteMock->expects(static::atLeastOnce())
            ->method('getShippingMethod')
            ->willReturn($currentCode);

        static::assertEquals($groups['mygroup'][3], $this->reviewBlock->getCurrentShippingRate());
    }

    private function getRateMock($code, $price = 5.95, $methodTitle = 'My shipping rate', $errorMessage = false)
    {
        $rateMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address\Rate::class)
            ->disableOriginalConstructor()
            ->setMethods(['getCode', 'getPrice', 'getMethodTitle', 'getErrorMessage'])
            ->getMock();

        $rateMock->expects(static::any())
            ->method('getCode')
            ->willReturn($code);

        $rateMock->expects(static::any())
            ->method('getPrice')
            ->willReturn($price);

        $rateMock->expects(static::any())
            ->method('getMethodTitle')
            ->willReturn($methodTitle);
        
        if ($errorMessage) {
            $rateMock->expects(static::any())
                ->method('getErrorMessage')
                ->willReturn($errorMessage);
        }

        return $rateMock;
    }

    /**
     * @param $code
     * @param $configValue
     * @param $expectedTitle
     * @dataProvider dataProviderTestGetCarrierName
     */
    public function testGetCarrierName($code, $configValue, $expectedTitle)
    {

        $this->scopeConfigMock->expects(static::once())
            ->method('getValue')
            ->with("carriers/{$code}/title", \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            ->willReturn($configValue);

        static::assertEquals($expectedTitle, $this->reviewBlock->getCarrierName($code));
    }

    public function dataProviderTestGetCarrierName()
    {
        return [
            [
                'code' => 'my_code',
                'configValue' => 'Some Title',
                'expectedTitle' => 'Some Title'
            ],
            [
                'code' => 'my_code',
                'configValue' => false,
                'expectedTitle' => 'my_code'
            ],
        ];
    }

    /**
     * @param $shippingPriceIncludingTax
     * @param $expectedOutput
     * @dataProvider dataProviderTestRenderShippingRateOption
     */
    public function testRenderShippingRateOption($rateTitle, $shippingPriceIncludingTax, $displayBothPrices, $expectedOutput, $error)
    {
        
        $this->taxHelper->expects(static::any())
            ->method('displayShippingPriceIncludingTax')
            ->willReturn($shippingPriceIncludingTax);

        $this->taxHelper->expects(static::any())
            ->method('displayShippingBothPrices')
            ->willReturn($displayBothPrices);
        
        $this->escaperMock->expects(static::any())
            ->method('escapeHtml')
            ->willReturnArgument(0);
        
        $this->taxHelper->expects(static::any())
            ->method('getShippingPrice')
            ->willReturnCallback(function ($price, $isInclTax) {
                if ($isInclTax) {
                    return $price + 0.5;
                }
                return $price;
            });
        
        $this->currencyMock->expects(static::any())
            ->method('convertAndFormat')
            ->willReturnCallback(function ($price) {
                return '$'.$price;
            });
        
        $rateMock = $this->getRateMock('my_code', 5.99, $rateTitle, $error);
        
        static::assertEquals($expectedOutput, $this->reviewBlock->renderShippingRateOption($rateMock));
    }

    public function dataProviderTestRenderShippingRateOption()
    {
        return [
            [
                'rateTitle' => 'My rate',
                'shippingPriceIncludingTax' => false,
                'displayBothPrices' => true,
                'expectedOutput' => 'My rate - $5.99 (Incl. Tax $6.49)',
                'error' => false,
            ],
            [
                'rateTitle' => 'My rate',
                'shippingPriceIncludingTax' => true,
                'displayBothPrices' => false,
                'expectedOutput' => 'My rate - $6.49',
                'error' => false,
            ],
            [
                'rateTitle' => 'My rate',
                'shippingPriceIncludingTax' => false,
                'displayBothPrices' => false,
                'expectedOutput' => 'My rate - $5.99',
                'error' => false,
            ],
            [
                'rateTitle' => 'My rate',
                'shippingPriceIncludingTax' => false,
                'displayBothPrices' => false,
                'expectedOutput' => 'My rate - Rate unavailable',
                'error' => 'Rate unavailable',
            ],
        ];
    }

    public function testVcButtonConfig()
    {
        $expectedValue = [
            'callId' => 'o24inv3o4vb',
            'apiKey' => 'NOPIVQNQENVNQEINVEVIOQEV',
        ];
        
        $this->configMock->expects(static::once())
            ->method('getApiKey')
            ->willReturn($expectedValue['apiKey']);
        
        $this->prepareQuoteMockForWithMethods(['getPayment', 'getAdditionalInformation']);
        
        $this->quoteMock->expects(static::once())
            ->method('getPayment')
            ->willReturnSelf();
        
        $this->quoteMock->expects(static::once())
            ->method('getAdditionalInformation')
            ->with(\AuthorizeNet\VisaCheckout\Model\Checkout::PARAM_CALL_ID)
            ->willReturn($expectedValue['callId']);
        
        static::assertEquals($expectedValue, $this->reviewBlock->getVcButtonConfig());
    }

    /**
     * @param $mode
     * @param $url
     * @dataProvider dataProviderTestGetVcButtonImageUrl
     */
    public function testGetVcButtonImageUrl($mode, $url)
    {
        
        $this->configMock->expects(static::once())
            ->method('isTestMode')
            ->willReturn($mode);
        
        static::assertEquals($url, $this->reviewBlock->getVcButtonImageUrl());
    }

    public function dataProviderTestGetVcButtonImageUrl()
    {
        return [
            ['mode' => true, 'url' => \AuthorizeNet\VisaCheckout\Block\Button::SANDBOX_BUTTON_URL],
            ['mode' => false, 'url' => \AuthorizeNet\VisaCheckout\Block\Button::LIVE_BUTTON_URL],
        ];
    }

    public function testGetPlaceOrderUrl()
    {

        $url = 'https://example.org/anet_visacheckout/checkout/place';

        $this->urlBuilderMock->expects(static::once())
            ->method('getUrl')
            ->with('anet_visacheckout/checkout/place', ['_secure' => true])
            ->willReturn($url);

        static::assertEquals($url, $this->reviewBlock->getPlaceOrderUrl());
    }

    public function testGetShippingMethodSubmitUrl()
    {
        $url = 'https://example.org/anet_visacheckout/checkout/saveShippingMethod';

        $this->urlBuilderMock->expects(static::once())
            ->method('getUrl')
            ->with('anet_visacheckout/checkout/saveShippingMethod', ['_secure' => true])
            ->willReturn($url);

        static::assertEquals($url, $this->reviewBlock->getShippingMethodSubmitUrl());
    }

    /**
     * @param $value
     * @dataProvider dataProviderTestAddressFormsVisibility
     */
    public function testAddressFormsVisibility($value)
    {
        $this->configMock->expects(static::any())
            ->method('isTelephoneRequired')
            ->willReturn($value);
        
        static::assertEquals($value, $this->reviewBlock->isTelephoneRequired());
        static::assertEquals($value, $this->reviewBlock->isBillingAddressFormVisible());
        static::assertEquals($value, $this->reviewBlock->isShippingAddressFormVisible());
    }

    public function dataProviderTestAddressFormsVisibility()
    {
        return [
            ['value' => true],
            ['value' => false],
        ];
    }

    /**
     * @param $code
     * @param $error
     * @dataProvider dataProviderTestRenderShippingRateValue
     */
    public function testRenderShippingRateValue($code, $error, $expected)
    {
        
        $rate = $this->getRateMock($code, 5.95, 'some title', $error);
        
        static::assertEquals($expected, $this->reviewBlock->renderShippingRateValue($rate));
    }

    public function dataProviderTestRenderShippingRateValue()
    {
        return [
            ['code' => 'my_code', 'error' => false, 'expected' => 'my_code'],
            ['code' => 'my_code', 'error' => 'Some error', 'expected' => ''],
        ];
    }


    public function testGetShippingMethodTemplate()
    {

        static::assertInternalType('string', $this->reviewBlock->getShippingMethodTemplate(), "Got a " . gettype($this->reviewBlock->getShippingMethodTemplate()) . " instead of a string");
    }
}
