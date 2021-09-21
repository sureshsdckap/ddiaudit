<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Gateway\Request;

use AuthorizeNet\Core\Gateway\Http\Client\AbstractClient;
use PHPUnit\Framework\TestCase;
use AuthorizeNet\Core\Gateway\Request\VaultTransactionRequestBuilder;

class VaultTransactionRequestBuilderTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var VaultTransactionRequestBuilder
     */
    protected $requestBuilder;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Gateway\Data\Order\OrderAdapter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderAdapterMock;

    /**
     * @var \Magento\Payment\Gateway\Data\AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $billingAddressAdapterMock;

    /**
     * @var \Magento\Payment\Gateway\Data\AddressAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shippingAddressAdapterMock;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;
    
    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->requestBuilder = new VaultTransactionRequestBuilder(
            $this->configMock,
            $this->subjectReaderMock,
            AbstractClient::TRANSACTION_AUTH_CAPTURE
        );
    }
    
    public function testBuild()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $subject['amount'] = 5.96;
        
        $orderData = [
            'increment_id' => '00000222',
            'base_tax_amount' => 1.99,
            'base_shipping_amount' => 5.00,
            'shipping_description' => 'My shipping method',
            'customer_id' => 1,
            'customer_email' => 'test@example.org',
            'base_currency_code' => 'EUR'
        ];
        
        $merchantData = [
            'login_id' => '423123123',
            'transaction_key' => 'lnO#h23g0h23g',
            'solution_id' => '42424242',
        ];
        
        $tokenData = [
            'customer_profile_id' => '123',
            'payment_profile_id' => '55215',
        ];
        
        $this->subjectReaderMock->method('readPayment')->willReturn($subject['payment']);
        $this->subjectReaderMock->method('readAmount')->willReturn($subject['amount']);

        $this->configMock->expects(static::atLeastOnce())->method('getLoginId')->willReturn($merchantData['login_id']);
        $this->configMock->expects(static::atLeastOnce())->method('getTransactionKey')->willReturn($merchantData['transaction_key']);
        $this->configMock->expects(static::atLeastOnce())->method('getSolutionId')->willReturn($merchantData['solution_id']);

        $this->orderMock->expects(static::once())->method('getBaseTaxAmount')->willReturn($orderData['base_tax_amount']);
        $this->orderMock->expects(static::once())->method('getBaseShippingAmount')->willReturn($orderData['base_shipping_amount']);
        $this->orderMock->expects(static::any())->method('getShippingDescription')->willReturn($orderData['shipping_description']);
        
        $this->orderAdapterMock->expects(static::any())->method('getOrderIncrementId')->willReturn($orderData['increment_id']);
        $this->orderAdapterMock->expects(static::any())->method('getCustomerId')->willReturn($orderData['customer_id']);
        $this->orderAdapterMock->expects(static::any())->method('getCurrencyCode')->willReturn($orderData['base_currency_code']);

        $this->billingAddressAdapterMock->expects(static::once())->method('getEmail')->willReturn($orderData['customer_email']);
        
        $paymentTokenMock = $this->getMockBuilder(\Magento\Vault\Model\PaymentToken::class)->disableOriginalConstructor()->getMock();
        $paymentTokenMock->expects(static::once())->method('getGatewayToken')->willReturn(implode(':', $tokenData));

        $extensionAttributesMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentExtensionInterface::class)
            ->setMethods(['getVaultPaymentToken'])
            ->getMockForAbstractClass();
        $extensionAttributesMock->expects(static::once())->method('getVaultPaymentToken')->willReturn($paymentTokenMock);
        
        $this->paymentMock->expects(static::once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        
        $this->orderAdapterMock->expects(static::once())->method('getItems')->willReturn([]);
        
        $request = $this->requestBuilder->build($subject);

        /* @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $request['request'];

        static::assertEquals(AbstractClient::TRANSACTION_AUTH_CAPTURE, $requestObject->getTransactionRequest()->getTransactionType());
        static::assertEquals(sprintf('%.2F', $subject['amount']), $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals($orderData['increment_id'], $requestObject->getTransactionRequest()->getOrder()->getInvoiceNumber());
        static::assertEquals($tokenData['customer_profile_id'], $requestObject->getTransactionRequest()->getProfile()->getCustomerProfileId());
        static::assertEquals($tokenData['payment_profile_id'], $requestObject->getTransactionRequest()->getProfile()->getPaymentProfile()->getPaymentProfileId());
        static::assertEquals($orderData['base_tax_amount'], $requestObject->getTransactionRequest()->getTax()->getAmount());
        static::assertEquals($orderData['base_shipping_amount'], $requestObject->getTransactionRequest()->getShipping()->getAmount());
        static::assertEquals($orderData['shipping_description'], $requestObject->getTransactionRequest()->getShipping()->getName());
        static::assertEquals($orderData['shipping_description'], $requestObject->getTransactionRequest()->getShipping()->getDescription());
        static::assertEquals($orderData['customer_id'], $requestObject->getTransactionRequest()->getCustomer()->getId());
        static::assertEquals($orderData['customer_email'], $requestObject->getTransactionRequest()->getCustomer()->getEmail());
        static::assertEquals($orderData['base_currency_code'], $requestObject->getTransactionRequest()->getCurrencyCode());

        static::assertEquals($merchantData['solution_id'], $requestObject->getTransactionRequest()->getSolution()->getId());
        static::assertEquals($merchantData['login_id'], $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($merchantData['transaction_key'], $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Invalid gateway token format
     */
    public function testBuildWithInvalidToken()
    {
        $subject['payment'] = $this->getPaymentDataObjectMock();
        $subject['amount'] = 5.96;

        $this->subjectReaderMock->method('readPayment')->willReturn($subject['payment']);
        $this->subjectReaderMock->method('readAmount')->willReturn($subject['amount']);

        $paymentTokenMock = $this->getMockBuilder(\Magento\Vault\Model\PaymentToken::class)->disableOriginalConstructor()->getMock();
        $paymentTokenMock->expects(static::once())->method('getGatewayToken')->willReturn('');

        $extensionAttributesMock = $this->getMockBuilder(\Magento\Sales\Api\Data\OrderPaymentExtensionInterface::class)
            ->setMethods(['getVaultPaymentToken'])
            ->getMockForAbstractClass();
        $extensionAttributesMock->expects(static::once())->method('getVaultPaymentToken')->willReturn($paymentTokenMock);
        $this->paymentMock->expects(static::once())->method('getExtensionAttributes')->willReturn($extensionAttributesMock);
        $request = $this->requestBuilder->build($subject);
    }

    private function getPaymentDataObjectMock()
    {
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass());

        $this->orderAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\Order\OrderAdapter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->billingAddressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)->getMockForAbstractClass();
        $this->shippingAddressAdapterMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\AddressAdapterInterface::class)->getMockForAbstractClass();
        
        $this->orderAdapterMock->expects(static::any())->method('getBillingAddress')->willReturn($this->billingAddressAdapterMock);
        $this->orderAdapterMock->expects(static::any())->method('getShippingAddress')->willReturn($this->shippingAddressAdapterMock);
        
        $this->paymentMock->expects(static::any())->method('getOrder')->willReturn($this->orderMock);

        $mock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObject::class)
            ->setMethods(['getPayment', 'getOrder'])
            ->disableOriginalConstructor()
            ->getMock();

        $mock->expects(static::once())->method('getPayment')->willReturn($this->paymentMock);

        $mock->expects(static::once())->method('getOrder')->willReturn($this->orderAdapterMock);

        return $mock;
    }
}
