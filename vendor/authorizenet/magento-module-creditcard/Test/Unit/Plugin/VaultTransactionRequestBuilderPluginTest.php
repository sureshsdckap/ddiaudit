<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Plugin;

use AuthorizeNet\CreditCard\Gateway\Config\Config;
use AuthorizeNet\CreditCard\Plugin\VaultTransactionRequestBuilderPlugin;
use PHPUnit\Framework\TestCase;

class VaultTransactionRequestBuilderPluginTest extends TestCase
{
    /**
     * @var VaultTransactionRequestBuilderPlugin
     */
    protected $model;
    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDOMock;
    /**
     * @var \Magento\Payment\Model\InfoInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;
    /**
     * @var \Magento\Payment\Model\MethodInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMethodMock;
    /**
     * @var Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \AuthorizeNet\Core\Gateway\Request\VaultTransactionRequestBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectMock;
    /**
     * @var \Callable|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $proceedMock;
    /**
     * @var \net\authorize\api\contract\v1\CreateTransactionRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $anetRequestMock;
    protected $result;
    protected $commandSubject;
    protected $paymentMethodCode;

    protected function setUp()
    {
        $this->anetRequestMock = $this->createMock(\net\authorize\api\contract\v1\CreateTransactionRequest::class);
        $this->result = [$this->anetRequestMock];
        $this->commandSubject = ['name' => 'value'];
        $this->paymentMethodCode = Config::VAULT_CODE;
        $this->subjectReaderMock = $this->createMock(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class);
        $this->paymentDOMock = $this->createMock(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);
        $this->paymentMethodMock = $this->createMock(\Magento\Payment\Model\MethodInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->subjectMock = $this->createMock(\AuthorizeNet\Core\Gateway\Request\VaultTransactionRequestBuilder::class);

        $this->subjectReaderMock->expects($this->once())
            ->method('readPayment')
            ->with($this->commandSubject)
            ->willReturn($this->paymentDOMock);

        $this->paymentDOMock->expects($this->once())
            ->method('getPayment')
            ->willReturn($this->paymentMock);

        $this->paymentMock->expects($this->once())
            ->method('getMethodInstance')
            ->willReturn($this->paymentMethodMock);

        $this->paymentMethodMock->expects($this->once())
            ->method('getCode')
            ->willReturnCallback([$this, 'getPaymentMethodCode']);

        $this->proceedMock = function () {
            return $this->result;
        };

        $this->model = new VaultTransactionRequestBuilderPlugin($this->subjectReaderMock, $this->configMock, true);
    }

    public function testPaymentMethodCode()
    {
        $this->paymentMethodCode = 'notVaultCode';
        $this->assertEquals($this->result, $this->model->aroundBuild($this->subjectMock, $this->proceedMock, $this->commandSubject));
    }

    /**
     * @dataProvider getVaultRequireCvvDataProvider
     */
    public function testGetVaultRequireCvv($isAdminArea, $method)
    {
        $this->model = new VaultTransactionRequestBuilderPlugin($this->subjectReaderMock, $this->configMock, $isAdminArea);
        $this->configMock->expects($this->any())
            ->method($method)
            ->willReturn(false);
        $this->assertEquals($this->result, $this->model->aroundBuild($this->subjectMock, $this->proceedMock, $this->commandSubject));
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage CVV is required
     */
    public function testCvvException()
    {
        $this->configMock->expects($this->any())
            ->method('getVaultAdminRequireCvv')
            ->willReturn(true);
        $this->model->aroundBuild($this->subjectMock, $this->proceedMock, $this->commandSubject);
    }

    public function testAroundBuild()
    {
        $this->configMock->expects($this->once())
            ->method('getVaultAdminRequireCvv')
            ->willReturn(true);
        $transactionRequest = $this->createMock(\net\authorize\api\contract\v1\TransactionRequestType::class);
        $profile = $this->createMock(\net\authorize\api\contract\v1\CustomerProfilePaymentType::class);
        $paymentProfile = $this->createMock(\net\authorize\api\contract\v1\PaymentProfileType::class);
        $cvv = '123';
        $this->paymentMock->expects($this->once())
            ->method('getAdditionalInformation')
            ->with(VaultTransactionRequestBuilderPlugin::KEY_INFO_VAULT_CVV)
            ->willReturn($cvv);
        $this->anetRequestMock->expects($this->once())
            ->method('getTransactionRequest')
            ->willReturn($transactionRequest);
        $transactionRequest->expects($this->once())
            ->method('getProfile')
            ->willReturn($profile);
        $profile->expects($this->once())
            ->method('getPaymentProfile')
            ->willReturn($paymentProfile);
        $paymentProfile->expects($this->once())
            ->method('setCardCode')
            ->with($cvv);
        $this->assertEquals(['request' => $this->anetRequestMock], $this->model->aroundBuild($this->subjectMock, $this->proceedMock, $this->commandSubject));
    }

    public function getPaymentMethodCode()
    {
        return $this->paymentMethodCode;
    }

    /**
     * @return array
     */
    public function getVaultRequireCvvDataProvider()
    {
        return [
            [true, 'getVaultAdminRequireCvv'],
            [false, 'getVaultRequireCvv']
        ];
    }
}
