<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Controller\Checkout;

class Place extends \Magento\Framework\App\Action\Action
{
    /**
     * @var $checkout
     */
    protected $checkout;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
    /**
     * @var \Magento\Checkout\Api\AgreementsValidatorInterface
     */
    private $agreementValidator;

    /**
     * Place Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \AuthorizeNet\PayPalExpress\Model\Checkout $checkout,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Api\AgreementsValidatorInterface $agreementValidator
    ) {

        $this->checkout = $checkout;
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->agreementValidator = $agreementValidator;
    }

    /**
     * Main action method.
     *
     * Place an order
     * Redirect customer to success page
     * Manage exception while placing an order
     * @return string
     */
    public function execute()
    {
        try {

            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));
            }

            if (!$this->agreementValidator->isValid(array_keys($this->getRequest()->getParam('agreement', [])))){
                throw new \Magento\Framework\Exception\LocalizedException(__('Please accept checkout agreements'));
            }

            $this->checkout->place();

            return $this->_redirect('checkout/onepage/success');
        } catch (\Magento\Framework\Exception\CouldNotSaveException $e) {
            $this->messageManager->addExceptionMessage($e, __('Unable to create order. Please try again later.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, 'An error occurred while placing order. Please try again later.');
        }

        return $this->_redirect('*/*/review');
    }
}
