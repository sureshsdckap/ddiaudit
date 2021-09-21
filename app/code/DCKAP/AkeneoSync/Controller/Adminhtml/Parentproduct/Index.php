<?php
namespace DCKAP\AkeneoSync\Controller\Adminhtml\Parentproduct;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;


class Index extends Action implements HttpGetActionInterface
{

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }


    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        //$resultPage->setActiveMenu(static::MENU_ID);
        $resultPage->getConfig()->getTitle()->set(__('Akeneo Configurable Product Sync'));

        return $resultPage;
    }
}
