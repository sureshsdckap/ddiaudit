<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Controller\Adminhtml\Merchant;

use Magento\Backend\App\Action\Context;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \AuthorizeNet\Core\Model\Merchant\Configurator
     */
    private $configurator;

    /**
     * Save Constructor
     *
     * @param Context                                        $context
     * @param \AuthorizeNet\Core\Model\Merchant\Configurator $configurator
     */
    public function __construct(Context $context, \AuthorizeNet\Core\Model\Merchant\Configurator $configurator)
    {
        parent::__construct($context);

        $this->configurator = $configurator;
    }

    /**
     * Save configuration data.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $resultRedirect->setPath('admin/system_config/edit/section/payment');

            $this->configurator->saveConfig($this->getRequest()->getParams());

            $this->messageManager->addSuccessMessage(__('Configuration has been saved!'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addExceptionMessage($e);
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Error while saving configuration.'));
        }

        return $resultRedirect;
    }
}
