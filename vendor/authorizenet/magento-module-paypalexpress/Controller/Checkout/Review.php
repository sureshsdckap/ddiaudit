<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Controller\Checkout;

class Review extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $checkout
     */
    protected $checkout;

    /**
     * Review Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
    ) {
        $this->checkout = $checkout;
        parent::__construct($context);
    }

    /**
     * Main action method.
     *
     * Update quote data and redirect to the result page.
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        try {
            $this->checkout->retrievePaypalCheckoutData();

            /** @var  \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);

            /** @var \AuthorizeNet\PayPalExpress\Block\Checkout\Review $reviewBlock */
            $reviewBlock = $resultPage->getLayout()->getBlock('paypalexpress.review');
            $reviewBlock->setQuote($this->checkout->getQuote());

            return $resultPage;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t initialize Express Checkout review.'));
        }

        return $this->_redirect('checkout/cart');
    }
}
