<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Model\Block\Customer;

use AuthorizeNet\CreditCard\Block\Customer\VaultCardRenderer;
use AuthorizeNet\CreditCard\Gateway\Config\Config;
use PHPUnit\Framework\TestCase;

class VaultCardRendererTest extends TestCase
{
    /**
     * @var VaultCardRenderer
     */
    protected $model;
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \Magento\Payment\Model\CcConfigProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configProviderMock;
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenMock;

    protected function setUp()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->configProviderMock = $this->createMock(\Magento\Payment\Model\CcConfigProvider::class);
        $this->configMock = $this->createMock(Config::class);
        $this->tokenMock = $this->getMockBuilder(\Magento\Vault\Api\Data\PaymentTokenInterface::class)
            ->setMethods(['getPaymentMethodCode','getTokenDetails'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->tokenMock->expects($this->once())
            ->method('getPaymentMethodCode')
            ->willReturn(Config::CODE);

        $this->tokenMock->expects($this->once())
            ->method('getTokenDetails')
            ->willReturn('{"cardNumber":"4111111111111111","cardExpMonth":"01","cardExpYear":"2020","cardType":"VI"}');

        $eventManager = $this->createMock(\Magento\Framework\Event\ManagerInterface::class);
        $scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $inlineTranslation = $this->createMock(\Magento\Framework\Translate\Inline\StateInterface::class);

        $this->contextMock->expects($this->once())
            ->method('getEventManager')
            ->willReturn($eventManager);

        $this->contextMock->expects($this->once())
            ->method('getScopeConfig')
            ->willReturn($scopeConfig);

        $this->contextMock->expects($this->once())
            ->method('getInlineTranslation')
            ->willReturn($inlineTranslation);

        $this->configProviderMock->expects($this->any())
            ->method('getIcons')
            ->willReturn(['VI' => [
                'url' => 'url',
                'width' => 10,
                'height' => 10
            ]]);

        $this->model = new VaultCardRenderer(
            $this->contextMock,
            $this->configProviderMock,
            $this->configMock,
            []
        );
    }

    public function testModel()
    {
        $this->model->render($this->tokenMock);
        $this->assertEquals(true, $this->model->canRender($this->tokenMock));
        $this->assertEquals($this->model::CARD_MASK . '-1111', $this->model->getNumberLast4Digits());
        $this->assertEquals('01/2020', $this->model->getExpDate());
        $this->assertEquals('url', $this->model->getIconUrl());
        $this->assertEquals(10, $this->model->getIconHeight());
        $this->assertEquals(10, $this->model->getIconWidth());
    }
}
