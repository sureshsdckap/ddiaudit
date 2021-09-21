<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

class Review extends AbstractCheckout
{

    /**
     * Main action method.
     *
     * Set the quote data and send it to order review page
     *
     * @return array
     */
    public function execute()
    {
        try {
            $this->initCheckout();
            $payment = $this->getQuote()->getPayment();
            $payment->setQuote($this->getQuote());
            $this->getQuote()->getPayment()->importData(['method' => \AuthorizeNet\VisaCheckout\Model\Ui\ConfigProvider::CODE]);

            $this->_view->loadLayout();
            
            /** @var  \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            
            /** @var \AuthorizeNet\VisaCheckout\Block\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('visacheckout.review');
            $reviewBlock->setQuote($this->getQuote());

            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t initialize Visa Checkout review.'));
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('checkout/cart');
    }
}
