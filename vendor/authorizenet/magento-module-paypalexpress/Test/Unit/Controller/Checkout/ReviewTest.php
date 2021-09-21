<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Controller\Checkout;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Controller\Checkout\Review;

class ReviewTest extends TestCase
{

    /**
     * @var \Magento\Framework\App\Action\Context|MockObject
     */
    protected $contextMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout|MockObject
     */
    protected $checkoutMock;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface|MockObject
     */
    protected $redirectMock;

    /**
     * @var \Magento\Framework\Message\ManagerInterface|MockObject
     */
    protected $messageManagerMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|MockObject
     */
    protected $responseMock;

    /**
     * @var \Magento\Framework\Controller\ResultFactory|MockObject
     */
    protected $resultFactoryMock;

    /**
     * @var \Magento\Framework\View\Result\Page|MockObject
     */
    protected $resultPageMock;

    /**
     * @var \Magento\Framework\View\LayoutInterface|MockObject
     */
    protected $layoutMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Block\Checkout\Review|MockObject
     */
    protected $reviewBlockMock;

    /**
     * @var \Magento\Quote\Model\Quote|MockObject
     */
    protected $quoteMock;

    /**
     * @var Review
     */
    protected $controller;


    protected function setUp()
    {

        $this->contextMock = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)->disableOriginalConstructor()->getMock();
        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Model\Checkout::class)->disableOriginalConstructor()->getMock();

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)->getMockForAbstractClass();
        $this->redirectMock = $this->getMockBuilder(\Magento\Framework\App\Response\RedirectInterface::class)->getMockForAbstractClass();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->getMockForAbstractClass();
        $this->resultFactoryMock = $this->getMockBuilder(\Magento\Framework\Controller\ResultFactory::class)->disableOriginalConstructor()->getMock();
        $this->resultPageMock = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()->getMock();
        $this->layoutMock = $this->getMockBuilder(\Magento\Framework\View\LayoutInterface::class)->getMockForAbstractClass();
        $this->reviewBlockMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Block\Checkout\Review::class)->disableOriginalConstructor()->getMock();
        $this->quoteMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();

        $this->contextMock->expects(static::any())->method('getResponse')->willReturn($this->responseMock);
        $this->contextMock->expects(static::any())->method('getRedirect')->willReturn($this->redirectMock);
        $this->contextMock->expects(static::any())->method('getMessageManager')->willReturn($this->messageManagerMock);
        $this->contextMock->expects(static::any())->method('getResultFactory')->willReturn($this->resultFactoryMock);

        $this->resultPageMock->expects(static::any())->method('getLayout')->willReturn($this->layoutMock);
        $this->checkoutMock->expects(static::any())->method('getQuote')->willReturn($this->quoteMock);

        $this->controller = new Review(
            $this->contextMock,
            $this->checkoutMock
        );
    }

    public function testExecute()
    {
        $this->checkoutMock->expects(static::once())->method('retrievePaypalCheckoutData');

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)
            ->willReturn($this->resultPageMock);

        $this->layoutMock->expects(static::once())->method('getBlock')->with('paypalexpress.review')->willReturn($this->reviewBlockMock);
        $this->reviewBlockMock->expects(static::once())->method('setQuote')->with($this->quoteMock)->willReturnSelf();

        static::assertEquals($this->resultPageMock, $this->controller->execute());
    }

    /**
     * @dataProvider dataProviderTestExecuteWithException
     */
    public function testExecuteWithException($expectedException, $expectedErrorMessage)
    {

        $this->checkoutMock->expects(static::once())->method('retrievePaypalCheckoutData')->willThrowException($expectedException);
        $this->redirectMock->expects(static::once())->method('redirect')->with($this->responseMock, 'checkout/cart', []);

        $this->messageManagerMock->expects(static::once())->method('addExceptionMessage')->with($expectedException, $expectedErrorMessage);

        static::assertEquals($this->responseMock, $this->controller->execute());
    }

    public function dataProviderTestExecuteWithException()
    {
        return [
            [
                'expectedException' => new \Magento\Framework\Exception\LocalizedException(__('Some')),
                'expectedErrorMessage' => null,
            ],
            [
                'expectedException' => new \Exception('Some'),
                'expectedErrorMessage' => 'We can\'t initialize Express Checkout review.',
            ],
        ];
    }
}
