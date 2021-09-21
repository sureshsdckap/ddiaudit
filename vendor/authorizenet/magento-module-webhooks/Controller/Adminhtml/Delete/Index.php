<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Controller\Adminhtml\Delete;

use AuthorizeNet\Webhooks\Model\Webhooks;
use Magento\Backend\App\Action\Context;

class Index extends \Magento\Backend\App\Action
{

    /**
     * @var Webhooks
     */
    protected $model;

    /**
     * Index constructor.
     * @param Context $context
     * @param Webhooks $model
     */
    public function __construct(
        Context $context,
        Webhooks $model
    ) {
        parent::__construct($context);
        $this->model = $model;
    }

    /**
     * Main action method to delete the webhook
     *
     * @return array
     */
    public function execute()
    {
        try {
            $result = $this->model->deleteWebhooks();

            if (count($result) == 0) {
                $this->messageManager->addNoticeMessage(__('No magento webhooks found.'));
            }

            foreach ($result as $name => $data) {
                if ($data['status'] == 200) {
                    $this->messageManager->addSuccessMessage(
                        __('Webhook %1 was unregistered successfully.', $name)
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        }

        return $this->resultRedirectFactory->create()->setPath('*/status/index');
    }
}
