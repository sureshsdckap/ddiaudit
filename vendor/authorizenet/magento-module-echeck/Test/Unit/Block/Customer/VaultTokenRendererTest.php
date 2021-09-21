<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Test\Unit\Block\Customer;

use AuthorizeNet\ECheck\Block\Customer\VaultTokenRenderer;
use AuthorizeNet\ECheck\Gateway\Config\Config;
use PHPUnit\Framework\TestCase;

class VaultTokenRendererTest extends TestCase
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
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenMock;

    public function testModel()
    {
        $routingNumber = '10001100010';
        $accountNumber = '01001110010';
        $accountName = 'Name';
        $accountType = 'checking';
        $echeckType = 'PPD';
        $details = [
            'routingNumber' => $routingNumber,
            'accountNumber' => $accountNumber,
            'accountName' => $accountName,
            'accountType' => $accountType,
            'echeckType' => $echeckType
        ];

        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
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
            ->willReturn(json_encode($details));

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


        $this->model = new VaultTokenRenderer(
            $this->contextMock,
            $this->configMock,
            []
        );

        $this->model->render($this->tokenMock);
        $this->assertEquals(true, $this->model->canRender($this->tokenMock));
        $this->assertEquals($this->model::ECHECK_MASK . '-' . $routingNumber, $this->model->getRoutingNumber());
        $this->assertEquals($this->model::ECHECK_MASK . '-' . $accountNumber, $this->model->getAccountNumber());
        $this->assertEquals($accountName, $this->model->getAccountName());
        $this->assertEquals($accountType, $this->model->getAccountType());
        $this->assertEquals('', $this->model->getIconUrl());
        $this->assertEquals('', $this->model->getIconHeight());
        $this->assertEquals('', $this->model->getIconWidth());
    }
}
