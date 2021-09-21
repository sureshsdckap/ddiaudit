<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Test\Unit\Block;

use AuthorizeNet\ECheck\Block\Payment;
use AuthorizeNet\ECheck\Model\Ui\ConfigProvider;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    /**
     * @var Payment
     */
    protected $payment;
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    public function testGetPaymentConfig()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->configMock = $this->createMock(ConfigProvider::class);
        $this->payment = new Payment(
            $this->contextMock,
            $this->configMock,
            []
        );
        $configValue = [
            'payment' => [
                \AuthorizeNet\Core\Gateway\Config\Config::CODE => [
                    'active' => true
                ]
            ]
        ];
        $expected = [
            'active' => true,
            'code' => \AuthorizeNet\Core\Gateway\Config\Config::CODE
        ];
        $expected['code'] = \AuthorizeNet\Core\Gateway\Config\Config::CODE;
        $this->configMock->expects($this->once())
            ->method('getConfig')
            ->willReturn($configValue);
        $this->configMock->expects($this->exactly(2))
            ->method('getCode')
            ->willReturn(\AuthorizeNet\Core\Gateway\Config\Config::CODE);
        $this->assertEquals(json_encode($expected, JSON_UNESCAPED_SLASHES), $this->payment->getPaymentConfig());
    }
}
