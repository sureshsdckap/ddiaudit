<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Controller\Checkout;

class SaveShippingMethod extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $checkout
     */
    protected $checkout;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * SaveShippingMethod constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \AuthorizeNet\PayPalExpress\Model\Checkout $checkout,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {

        $this->checkout = $checkout;
        $this->formKeyValidator = $formKeyValidator;
        parent::__construct($context);
    }

    /**
     * Main action method.
     * Update the shipping method and change the shipping method section
     *
     * @return string
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->getParam('isAjax');
        try {

            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));
            }

            $this->checkout->updateShippingMethod($this->getRequest()->getParam('shipping_method'));
            if ($isAjax) {
                $this->_view->loadLayout('anet_paypal_express_review_details', true, true, false);
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
