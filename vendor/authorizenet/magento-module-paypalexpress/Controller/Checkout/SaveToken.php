<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Controller\Checkout;

class SaveToken extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout
     */
    private $checkout;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * Initialize Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \AuthorizeNet\PayPalExpress\Model\Checkout $checkout,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->checkout = $checkout;
        $this->formKeyValidator = $formKeyValidator;

        parent::__construct($context);
    }

    /**
     * SaveToken Main action
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid form key'));
            }

            $this->checkout->saveTokenData(
                [
                    'token' => $this->getRequest()->getParam('token'),
                    'transId' => $this->getRequest()->getParam('transId'),
                ]
            );

            $result->setData([
                'status' => true,
            ]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData([
                'status' => false,
                'error' => $e->getMessage(),
            ]);
        } catch (\Exception $e) {
            $result->setData([
                'status' => false,
                'error' => __('We are unable to initialize Paypal Express Checkout.')
            ]);
        }

        return $result;
    }
}
