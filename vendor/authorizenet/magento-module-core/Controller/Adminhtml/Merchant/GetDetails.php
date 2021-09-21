<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Controller\Adminhtml\Merchant;

use Magento\Backend\App\Action\Context;

class GetDetails extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator
     */
    private $configurator;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config
     */
    private $config;

    /**
     * @var \AuthorizeNet\VisaCheckout\Gateway\Config\Config
     */
    private $visaCheckoutConfig;

    /**
     * GetDetails Constructor
     *
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \AuthorizeNet\Core\Model\Merchant\Configurator $configurator
     * @param \AuthorizeNet\Core\Gateway\Config\Config $config
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \AuthorizeNet\Core\Model\Merchant\Configurator $configurator,
        \AuthorizeNet\Core\Gateway\Config\Config $config
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->configurator = $configurator;
        $this->config = $config;
    }

    /**
     * Retrieve the configuration data
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute()
    {
        $loginId = $this->getRequest()->getParam('login_id');
        $transactionKey = $this->getRequest()->getParam('transaction_key');
        $mode = $this->getRequest()->getParam('sandbox_mode') === 'true' ? true : false;

        $result = $this->resultJsonFactory->create();

        if ($transactionKey == \AuthorizeNet\Core\Model\Merchant\DataProvider::MASKED_VALUE) {
            $transactionKey = $this->config->getTransKey();
        }

        try {
            $this->config->setSandboxMode($mode);
            $details = $this->configurator->loadConfig($loginId, $transactionKey);
            if ($loginId == $this->config->getLoginId() && $transactionKey == $this->config->getTransKey()) {
                $details['data.signature_key']= $this->config->getSignatureKey();
            }
            $result->setData(['status' => true, 'details' => $details]);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result->setData(['status' => false, 'message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $result->setData(['status' => false, 'message' => __('Unable to load configuration')]);
        }

        return $result;
    }
}
