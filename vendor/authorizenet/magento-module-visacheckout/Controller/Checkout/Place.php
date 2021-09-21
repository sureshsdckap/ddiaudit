<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Controller\Checkout;

class Place extends AbstractCheckout
{

    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    private $agreementValidator;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \AuthorizeNet\VisaCheckout\Model\Checkout $checkout,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
    ) {
        parent::__construct($context, $checkout, $checkoutSession, $customerSession, $formKeyValidator);
        $this->agreementValidator = $agreementValidator;
    }

    /**
     * Main action method.
     *
     * Action to update billing and shipping address info and redirect to success page.
     *
     * @return string
     */
    public function execute()
    {

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        try {

            if (!$this->agreementValidator->isValid(array_keys($this->getRequest()->getParam('agreement', [])))){
                throw new \Magento\Framework\Exception\LocalizedException(__('Please accept checkout agreements'));
            }

            $this->initCheckout();

            if ($shippingParam = $this->getRequest()->getParam('shipping_address')) {
                parse_str($shippingParam, $shippingData);
                $this->checkout->updateShippingAddressData($shippingData);
            }
            
            if ($billingParam = $this->getRequest()->getParam('billing_address')) {
                parse_str($billingParam, $billingData);
                $this->checkout->updateBillingAddressData($billingData);
            }
            
            $this->checkout->place();
            
            return $this->_redirect('checkout/onepage/success');
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addErrorMessage(__('Unable to create order. Please try again later.'));
            return $this->_redirect('*/*/review');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/review');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('An error occurred while placing order. Please try again later.');
            return $this->_redirect('*/*/review');
        }
    }
}
