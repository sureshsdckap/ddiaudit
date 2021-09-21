<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Block;

use AuthorizeNet\CreditCard\Model\Ui\ConfigProvider;
use AuthorizeNet\CreditCard\Gateway\Config\Config;
use Magento\Framework\View\Element\Template\Context;
use AuthorizeNet\CreditCard\Block\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var Payment
     */
    protected $payment;

    public function setUp()
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(ConfigProvider::class)
            ->setMethods(['getConfig', 'getCode'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->payment = new Payment(
            $this->contextMock,
            $this->configMock,
            []
        );
    }

    /**
     * @param array $config
     * @param array $expected
     * @dataProvider getPaymentConfigDataProvider
     */
    public function testGetPaymentConfig($value, $expected)
    {
        $this->configMock->expects(static::exactly(2))
            ->method('getCode')
            ->willReturn(Config::CODE);

        $this->configMock->expects(static::once())
            ->method('getConfig')
            ->willReturn($value);

        static::assertEquals(
            $expected,
            $this->payment->getPaymentConfig()
        );
    }

    public function testGetCode()
    {
        $this->configMock->expects(static::once())
            ->method('getCode')
            ->willReturn(Config::CODE);

        static::assertEquals(
            Config::CODE,
            $this->payment->getCode()
        );
    }

    /**
     * @return array
     */
    public function getPaymentConfigDataProvider()
    {
        return [
            [
                [
                    'payment' => [
                        Config::CODE => [
                            'isActive' => true,
                            'getTitle' => 'Credit Card',
                            'getAvailableCardTypes' => ['CUP', 'AE', 'VI', 'MC', 'DI', 'JCB', 'DN', 'MI'],
                            'getLoginId' => '10gin1D',
                            'getClientKey' => 'c1i3n7Key'
                        ]
                    ],
                ],
                '{"isActive":true,"getTitle":"Credit Card","getAvailableCardTypes":["CUP","AE","VI","MC","DI","JCB","DN","MI"],"getLoginId":"10gin1D","getClientKey":"c1i3n7Key","code":"' . Config::CODE . '"}'
            ]
        ];
    }
}
