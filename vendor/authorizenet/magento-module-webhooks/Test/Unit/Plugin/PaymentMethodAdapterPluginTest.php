<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Plugin;

use AuthorizeNet\Webhooks\Plugin\PaymentMethodAdapterPlugin;
use PHPUnit\Framework\TestCase;

class PaymentMethodAdapterPluginTest extends TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\Adapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Info|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;
    /**
     * @var \AuthorizeNet\Webhooks\Payload\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var \Callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proceedMock;
    /**
     * @var bool
     */
    protected $isProceedMockCalled = false;
    /**
     * @var PaymentMethodAdapterPlugin
     */
    protected $model;
    protected $amount;

    protected function setUp()
    {
        $this->subjectMock = $this->getMockBuilder(\Magento\Payment\Model\Method\Adapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Info::class)
            ->disableOriginalConstructor()
            ->setMethods(['getMethod', 'getSkipGatewayCommand'])
            ->getMock();

        $this->proceedMock = function () {
            $this->isProceedMockCalled = true;
        };

        $this->isProceedMockCalled = false;

        $this->model = new PaymentMethodAdapterPlugin(['paymentMethod']);
    }

    /**
     * @dataProvider pluginDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundRefund($method, $expect, $skipGatewayCommand, $shouldProceedRun)
    {
        $this->paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        $this->paymentMock->expects($this->exactly($expect))
            ->method('getSkipGatewayCommand')
            ->willReturn($skipGatewayCommand);
        $this->model->aroundRefund($this->subjectMock, $this->proceedMock, $this->paymentMock, 10);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * @dataProvider pluginDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundCapture($method, $expect, $skipGatewayCommand, $shouldProceedRun)
    {
        $this->paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        $this->paymentMock->expects($this->exactly($expect))
            ->method('getSkipGatewayCommand')
            ->willReturn($skipGatewayCommand);
        $this->model->aroundCapture($this->subjectMock, $this->proceedMock, $this->paymentMock, 10);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * @dataProvider pluginDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundVoid($method, $expect, $skipGatewayCommand, $shouldProceedRun)
    {
        $this->paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        $this->paymentMock->expects($this->exactly($expect))
            ->method('getSkipGatewayCommand')
            ->willReturn($skipGatewayCommand);
        $this->model->aroundVoid($this->subjectMock, $this->proceedMock, $this->paymentMock);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * @dataProvider pluginDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundAcceptPayment($method, $expect, $skipGatewayCommand, $shouldProceedRun)
    {
        $this->paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        $this->paymentMock->expects($this->exactly($expect))
            ->method('getSkipGatewayCommand')
            ->willReturn($skipGatewayCommand);
        $this->model->aroundAcceptPayment($this->subjectMock, $this->proceedMock, $this->paymentMock);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * @dataProvider pluginDataProvider
     * @param bool $isDisabled
     * @param bool $shouldProceedRun
     */
    public function testAroundDenyPayment($method, $expect, $skipGatewayCommand, $shouldProceedRun)
    {
        $this->paymentMock->expects($this->once())
            ->method('getMethod')
            ->willReturn($method);
        $this->paymentMock->expects($this->exactly($expect))
            ->method('getSkipGatewayCommand')
            ->willReturn($skipGatewayCommand);
        $this->model->aroundDenyPayment($this->subjectMock, $this->proceedMock, $this->paymentMock);
        $this->assertEquals($shouldProceedRun, $this->isProceedMockCalled);
    }

    /**
     * @return array
     */
    public function pluginDataProvider()
    {
        return [
            ['excludePaymentMethod', 0, true, true],
            ['paymentMethod', 1, true, false],
            ['paymentMethod', 1, false, true]
        ];
    }
}
