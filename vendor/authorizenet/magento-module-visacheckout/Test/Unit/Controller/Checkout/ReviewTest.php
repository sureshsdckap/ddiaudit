<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Test\Unit\Controller\Checkout;

use AuthorizeNet\VisaCheckout\Controller\Checkout\Review;

class ReviewTest extends AbstractControllerTest
{

    protected function setUp()
    {
        parent::setUp();
        
        $this->controller = new Review(
            $this->contextMock,
            $this->checkoutMock,
            $this->checkoutSessionMock,
            $this->customerSessionMock,
            $this->formkeyValidatorMock
        );
    }

    public function testEmptyCart()
    {
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects(static::once())->method('setPath')->with('checkout/cart')->willReturnSelf();

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with(
                static::isInstanceOf('\Magento\Framework\Exception\LocalizedException'),
                'We can\'t initialize Visa Checkout review.'
            );
        
        static::assertEquals($resultRedirect, $this->controller->execute());
    }

    public function testGenericException()
    {
        $this->prepareCartItems();
        
        $resultRedirect = $this->getMockBuilder(\Magento\Framework\Controller\Result\Redirect::class)->disableOriginalConstructor()->getMock();
        $resultRedirect->expects(static::once())->method('setPath')->with('checkout/cart')->willReturnSelf();

        $exception = new \Exception('Some unpredictable stuff happened');
        
        $this->viewMock->expects(static::once())->method('loadLayout')->willThrowException($exception);
        
        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->messagesManagerMock->expects(static::once())
            ->method('addExceptionMessage')
            ->with($exception, __('We can\'t initialize Visa Checkout review.'));

        static::assertEquals($resultRedirect, $this->controller->execute());
    }
    
    public function testExecute()
    {
        $this->prepareCartItems();
        
        $blockMock = $this->getMockBuilder(\AuthorizeNet\VisaCheckout\Block\Checkout\Review::class)->disableOriginalConstructor()->getMock();
        $blockMock->expects(static::once())->method('setQuote')->with($this->quoteMock)->willReturnSelf();

        $this->layoutMock->expects(static::once())->method('getBlock')->with('visacheckout.review')->willReturn($blockMock);

        $pageResult = $this->getMockBuilder(\Magento\Framework\View\Result\Page::class)->disableOriginalConstructor()->getMock();
        $pageResult->expects(static::any())->method('getLayout')->willReturn($this->layoutMock);

        $this->viewMock->expects(static::once())->method('loadLayout')->willReturnSelf();

        $this->paymentMock->expects(static::once())
            ->method('importData')
            ->with(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE]);

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->with(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE)
            ->willReturn($pageResult);
        
        static::assertEquals($pageResult, $this->controller->execute());
    }
}
