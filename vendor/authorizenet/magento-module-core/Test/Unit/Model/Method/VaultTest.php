<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\AuthorizeNet\Core\Model\Method;

use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Model\Method\Vault;

/**
 * Class VaultTest
 * @package AuthorizeNet\Core\Test\Unit\AuthorizeNet\Core\Model\Method
 */
class VaultTest extends TestCase
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Payment\Gateway\ConfigFactoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configFactoryMock;
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;
    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $vaultProviderMock;
    /**
     * @var \Magento\Framework\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManagerMock;
    /**
     * @var \Magento\Payment\Gateway\Config\ValueHandlerPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueHandlerPoolMock;
    /**
     * @var \Magento\Payment\Gateway\Command\CommandManagerPoolInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandManagerPoolMock;
    /**
     * @var \Magento\Vault\Api\PaymentTokenManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenManagementMock;
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentExtensionFactoryMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;

    protected $code = 'my_vault_code';

    /**
     * @var \Magento\Payment\Gateway\Config\ValueHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $valueHandlerMock;

    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoInstanceMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectMock;

    /**
     * @var Vault
     */
    protected $vault;


    protected function setUp()
    {


        $this->configMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigInterface::class)->getMockForAbstractClass();
        $this->configFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\ConfigFactoryInterface::class)->getMockForAbstractClass();
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)->getMockForAbstractClass();
        $this->vaultProviderMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();
        $this->eventManagerMock = $this->getMockBuilder(\Magento\Framework\Event\ManagerInterface::class)->getMockForAbstractClass();
        $this->valueHandlerPoolMock = $this->getMockBuilder(\Magento\Payment\Gateway\Config\ValueHandlerPoolInterface::class)->getMockForAbstractClass();
        $this->commandManagerPoolMock = $this->getMockBuilder(\Magento\Payment\Gateway\Command\CommandManagerPoolInterface::class)->getMockForAbstractClass();
        $this->tokenManagementMock = $this->getMockBuilder(\Magento\Vault\Api\PaymentTokenManagementInterface::class)->getMockForAbstractClass();
        $this->paymentExtensionFactoryMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory::class)->getMockForAbstractClass();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory::class)->disableOriginalConstructor()->getMock();

        $this->valueHandlerMock = $this->getMockBuilder(\Magento\Payment\Gateway\Config\ValueHandlerInterface::class)->getMockForAbstractClass();
        $this->infoInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\InfoInterface::class)->getMockForAbstractClass();
        $this->paymentDataObjectMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)->getMockForAbstractClass();

        $this->vault = new Vault(
            $this->configMock,
            $this->configFactoryMock,
            $this->objectManagerMock,
            $this->vaultProviderMock,
            $this->eventManagerMock,
            $this->valueHandlerPoolMock,
            $this->commandManagerPoolMock,
            $this->tokenManagementMock,
            $this->paymentExtensionFactoryMock,
            $this->paymentDataObjectFactoryMock,
            $this->code
        );
    }

    public function testCanFetchTransactionInfo()
    {
        $this->valueHandlerPoolMock->expects(static::any())->method('get')->with('can_fetch_transaction_info')->willReturn($this->valueHandlerMock);
        $this->vaultProviderMock->expects(static::any())->method('getInfoInstance')->willReturn($this->infoInstanceMock);

        $expectedValue = true;

        $subject = [
            'field' => 'can_fetch_transaction_info',
            'payment' => $this->paymentDataObjectMock,
        ];

        $this->paymentDataObjectFactoryMock->expects(static::any())->method('create')->with($this->infoInstanceMock)->willReturn($this->paymentDataObjectMock);

        $this->valueHandlerMock->expects(static::once())->method('handle')->with($subject)->willReturn($expectedValue);

        static::assertEquals($expectedValue, $this->vault->canFetchTransactionInfo());
    }

    public function testFetchTransactionInfo()
    {

        $transactionId = '1234556789';
        $expectedInfo = ['somkey' => 'somevalue'];

        $this->vaultProviderMock->expects(static::any())->method('fetchTransactionInfo')->with($this->infoInstanceMock, $transactionId)->willReturn($expectedInfo);

        static::assertEquals($expectedInfo, $this->vault->fetchTransactionInfo($this->infoInstanceMock, $transactionId));
    }
}
