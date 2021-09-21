<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */
namespace AuthorizeNet\Webhooks\Test\Unit\Model\Payment;

use AuthorizeNet\Webhooks\Model\Payment\Webhook;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    /**
     * @var Webhook
     */
    protected $model;
    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;
    /**
     * @var \Magento\Framework\DataObject|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $dataMock;
    /**
     * @var \Magento\Quote\Api\Data\CartInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $cartMock;

    protected function setUp()
    {
        $this->configMock = $this->createMock(\AuthorizeNet\Core\Gateway\Config\Config::class);
        $this->paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);
        $this->dataMock = $this->createMock(\Magento\Framework\DataObject::class);
        $this->cartMock = $this->createMock(\Magento\Quote\Api\Data\CartInterface::class);

        $this->configMock->expects(static::once())
            ->method('getConfigValue')
            ->with('field', 1)
            ->willReturn('fieldValue');

        $this->model = new Webhook($this->configMock);
    }

    public function testModel()
    {
        $this->assertEquals(Webhook::METHOD_CODE, $this->model->getCode());
        $this->assertEquals(null, $this->model->getFormBlockType());
        $this->assertEquals('Webhook', $this->model->getTitle());
        $this->model->setStore(1);
        $this->model->getStore();
        $this->assertEquals(false, $this->model->canOrder());
        $this->assertEquals(false, $this->model->canAuthorize());
        $this->assertEquals(false, $this->model->canCapture());
        $this->assertEquals(false, $this->model->canCapturePartial());
        $this->assertEquals(false, $this->model->canCaptureOnce());
        $this->assertEquals(false, $this->model->canRefund());
        $this->assertEquals(false, $this->model->canRefundPartialPerInvoice());
        $this->assertEquals(false, $this->model->canVoid());
        $this->assertEquals(false, $this->model->canUseInternal());
        $this->assertEquals(false, $this->model->canUseCheckout());
        $this->assertEquals(false, $this->model->canEdit());
        $this->assertEquals(false, $this->model->canFetchTransactionInfo());
        $this->assertEquals([], $this->model->fetchTransactionInfo($this->paymentMock, 1));
        $this->assertEquals(false, $this->model->isGateway());
        $this->assertEquals(true, $this->model->isOffline());
        $this->assertEquals(false, $this->model->isInitializeNeeded());
        $this->assertEquals(true, $this->model->canUseForCountry(''));
        $this->assertEquals(true, $this->model->canUseForCurrency(''));
        $this->assertEquals('', $this->model->getInfoBlockType());
        $this->assertEquals(null, $this->model->getInfoInstance());
        $this->assertEquals($this->model, $this->model->setInfoInstance($this->paymentMock));
        $this->assertEquals($this->model, $this->model->validate());
        $this->assertEquals($this->model, $this->model->order($this->paymentMock, 10));
        $this->assertEquals($this->model, $this->model->authorize($this->paymentMock, 10));
        $this->assertEquals($this->model, $this->model->capture($this->paymentMock, 10));
        $this->assertEquals($this->model, $this->model->refund($this->paymentMock, 10));
        $this->assertEquals($this->model, $this->model->cancel($this->paymentMock));
        $this->assertEquals($this->model, $this->model->void($this->paymentMock));
        $this->assertEquals(false, $this->model->canReviewPayment());
        $this->assertEquals(false, $this->model->acceptPayment($this->paymentMock));
        $this->assertEquals(false, $this->model->denyPayment($this->paymentMock));
        $this->assertEquals('fieldValue', $this->model->getConfigData('field', 1));
        $this->assertEquals($this->model, $this->model->assignData($this->dataMock));
        $this->assertEquals(false, $this->model->isAvailable($this->cartMock));
        $this->assertEquals(true, $this->model->isActive(1));
        $this->assertEquals($this->model, $this->model->initialize('paymentAction', new \stdClass()));
        $this->assertEquals(null, $this->model->getConfigPaymentAction());
    }
}
