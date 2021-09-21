<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Response;

use PHPUnit\Framework\TestCase;
use \AuthorizeNet\Core\Gateway\Response\ProfileDetailsHandler;

class ProfileDetailsHandlerTest extends TestCase
{

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentExtensionFactoryMock;
    /**
     * @var \Magento\Vault\Model\CreditCardTokenFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenFactoryMock;
    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDoMock;

    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $methodInstanceMock;

    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenMock;

    /**
     * @var \Magento\Vault\Model\PaymentTokenManagement|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentTokenManagementMock;

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $extensionAttributesMock;

    /**
     * @var ProfileDetailsHandler
     */
    protected $handler;

    /**
     * @var \AuthorizeNet\Core\Model\CcTypes
     */
    protected $ccTypesMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;


    protected function setUp()
    {
        $this->paymentExtensionFactoryMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory::class)->setMethods(['create'])->getMockForAbstractClass();
        $this->paymentTokenManagementMock = $this->getMockBuilder(\Magento\Vault\Model\PaymentTokenManagement::class)->disableOriginalConstructor()->getMock();
        $this->paymentTokenFactoryMock = $this->getMockBuilder(\Magento\Vault\Model\CreditCardTokenFactory::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->ccTypesMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\CcTypes::class)->disableOriginalConstructor()->getMock();

        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentDoMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)->getMockForAbstractClass();
        $this->paymentTokenMock = $this->getMockBuilder(\Magento\Vault\Api\Data\PaymentTokenInterface::class)->getMockForAbstractClass();
        $this->methodInstanceMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();
        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)->disableOriginalConstructor()->getMock();

        $this->extensionAttributesMock = $this
            ->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentExtensionInterface::class)
            ->setMethods(['getVaultPaymentToken', 'setVaultPaymentToken'])
            ->getMockForAbstractClass();

        $this->paymentExtensionFactoryMock->expects(static::any())->method('create')->willReturn($this->extensionAttributesMock);
        $this->paymentTokenFactoryMock->expects(static::any())->method('create')->willReturn($this->paymentTokenMock);
        $this->paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($this->methodInstanceMock);
        $this->paymentDoMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);
        $this->subjectReaderMock->expects(static::any())->method('readPayment')->willReturn($this->paymentDoMock);

        $this->handler = new ProfileDetailsHandler(
            $this->paymentExtensionFactoryMock,
            $this->paymentTokenFactoryMock,
            $this->paymentTokenManagementMock,
            $this->subjectReaderMock,
            $this->ccTypesMock
        );
    }

    public function testHandle()
    {

        $subject = ['payment' => $this->paymentDoMock];

        $responseMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CreateCustomerProfileResponse::class)->getMock();

        $this->subjectReaderMock->expects(static::any())->method('readCreateCustomerProfileResponseObject')->willReturn($responseMock);

        $token = '123123';
        $loginId = 'qwd14124';
        $customerProfileId = '1212442';
        $paymentAdditionalInformation = [
            'cardExpYear' => '2021',
            'cardExpMonth' => '01',
            'cardType' => 'MasterCard'
        ];

        $responseMock->expects(static::any())->method('getCustomerProfileId')->willReturn($customerProfileId);
        $responseMock->expects(static::any())->method('getCustomerPaymentProfileIdList')->willReturn([$token]);

        $this->methodInstanceMock->expects(static::any())->method('getConfigData')->with(\AuthorizeNet\Core\Gateway\Config\Config::KEY_LOGIN_ID)->willReturn($loginId);

        $this->paymentMock->expects(static::any())
            ->method('getAdditionalInformation')
            ->willReturnCallback(function ($key) use ($paymentAdditionalInformation) {
                if ($key == null) {
                    return $paymentAdditionalInformation;
                }
                return $paymentAdditionalInformation[$key] ?? null;
            });

        $this->paymentMock->expects(static::any())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        //Token data expectation
        $this->paymentTokenMock->expects(static::once())->method('setExpiresAt')->with('2021-02-01 00:00:00')->willReturnSelf();
        $this->paymentTokenMock->expects(static::once())->method('setGatewayToken')->with(implode(':', [$customerProfileId, $token, $loginId]))->willReturnSelf();

        $this->handler->handle($subject, [$responseMock]);
    }
}
