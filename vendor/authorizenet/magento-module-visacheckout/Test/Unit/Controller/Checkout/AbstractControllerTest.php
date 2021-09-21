<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Controller\Checkout;

use PHPUnit\Framework\TestCase;

abstract class AbstractControllerTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \AuthorizeNet\VisaCheckout\Model\Checkout|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutMock;

    /**
     * @var \Magento\Checkout\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $checkoutSessionMock;

    /**
     * @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerSessionMock;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $formkeyValidatorMock;

    /**
     * @var \Magento\Quote\Model\Quote|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $quoteMock;

    /**
     * @var \Magento\Framework\App\Response\Http|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messagesManagerMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultFactoryMock;


    /**
     * @var \Magento\Quote\Model\Quote\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Framework\App\ViewInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $viewMock;

    /**
     * @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $redirectMock;
    
    /**
     * @var \Magento\Framework\View\LayoutInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $layoutMock;

    /**
     * @var \Magento\Framework\UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlMock;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultRedirectFactory;
    
    /**
     * @var \Magento\Framework\App\ActionInterface
     */
    protected $controller;
    

    protected function setUp()
    {

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http::class)->disableOriginalConstructor()->getMock();

        $this->messagesManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();

        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)->disableOriginalConstructor()->getMock();

        $this->requestMock = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)->getMockForAbstractClass();
        
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMockForAbstractClass();
        
        $this->resultRedirectFactory = $this->getMockBuilder(\Magento\Framework\Controller\Result\RedirectFactory::class)->disableOriginalConstructor()->getMock();

        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        
        $this->urlMock = $this->getMockBuilder(\Magento\Framework\UrlInterface::class)->getMockForAbstractClass();

        $this->viewMock = $this->getMockBuilder(\Magento\Framework\App\ViewInterface::class)->getMockForAbstractClass();
        $this->viewMock->expects(static::any())->method('getLayout')->willReturn($this->layoutMock);
        
        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->contextMock->expects(static::any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messagesManagerMock);
        $this->contextMock->expects(static::any())->method('getResultFactory')->willReturn($this->resultFactoryMock);
        $this->contextMock->expects(static::any())->method('getView')->willReturn($this->viewMock);
        $this->contextMock->expects(static::any())->method('getRequest')->willReturn($this->requestMock);
        $this->contextMock->expects(static::any())->method('getRedirect')->willReturn($this->redirectMock);
        $this->contextMock->expects(static::any())->method('getUrl')->willReturn($this->urlMock);
        $this->contextMock->expects(static::any())->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);

        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Model\Checkout::class)->disableOriginalConstructor()->getMock();
        $this->checkoutMock->expects(static::any())->method('setQuote')->willReturnSelf();
        $this->checkoutMock->expects(static::any())->method('setCustomerSession')->willReturnSelf();

        $this->paymentMock = $this->getMockBuilder(\Magento\Quote\Model\Quote\Payment::class)->disableOriginalConstructor()->getMock();

        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();
        $this->quoteMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);

        $this->checkoutSessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)->disableOriginalConstructor()->getMock();
        $this->checkoutSessionMock->expects(static::any())->method('getQuote')->willReturn($this->quoteMock);

        $this->customerSessionMock = $this->getMockBuilder(\Magento\Customer\Model\Session::class)->disableOriginalConstructor()->getMock();

        $this->formkeyValidatorMock = $this->getMockBuilder(\Magento\Framework\Data\Form\FormKey\Validator::class)->disableOriginalConstructor()->getMock();
    }

    protected function prepareCartItems()
    {
        $this->quoteMock->expects(static::any())
            ->method('hasItems')
            ->willReturn(true);
    }
}
