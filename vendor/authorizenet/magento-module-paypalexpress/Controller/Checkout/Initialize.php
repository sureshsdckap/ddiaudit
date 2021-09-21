<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use AuthorizeNet\PayPalExpress\Gateway\Command\InitializeCommand;
use Magento\Checkout\Model\Session;

class Initialize extends \Magento\Framework\App\Action\Action
{

    /**
     * @var JsonFactory $resultJsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var InitializeCommand
     */
    private $command;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout
     */
    private $checkout;

    /**
     * Initialize Constructor
     *
     * @param Context                                    $context
     * @param JsonFactory                                $jsonFactory
     * @param InitializeCommand                          $command
     * @param Session                                    $session
     * @param \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        InitializeCommand $command,
        Session $session,
        \AuthorizeNet\PayPalExpress\Model\Checkout $checkout
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->command = $command;
        $this->session = $session;
        $this->checkout = $checkout;

        parent::__construct($context);
    }

    /**
     * Initialize Paypal Express Checkout
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            if (! $tokenData = $this->checkout->getTokenData()) {
                $commandSubject = [];

                if ($this->getRequest()->getParam('ignore_shipping')) {
                    $commandSubject['ignoreShippingAddress'] = true;
                }

                $tokenData = $this->command->execute($commandSubject)->get();
            }

            $result->setData([
                'status' => true,
                'data' => $tokenData
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
