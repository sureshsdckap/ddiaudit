<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Controller\Checkout;

class SaveShippingMethod extends AbstractCheckout
{
    /**
     * Main action method.
     *
     * Update and save shipping method.
     * If have ajax data then return valid response otherwise redirect review page.
     *
     * @return \Magento\Framework\Controller\ResultInterface|\Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->getParam('isAjax');
        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));
            }

            $this->initCheckout();
            $this->checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->_view->loadLayout('authorizenetvisa_review_details', true, true, false);
                return $this->getResponse()->setBody(
                    $this->_view->getLayout()->getBlock('page.block')->toHtml()
                );
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('We can\'t update shipping method.'));
        }
        if ($isAjax) {
            return $this->getResponse();
        } else {
            return $this->_redirect('*/*/review');
        }
    }
}
