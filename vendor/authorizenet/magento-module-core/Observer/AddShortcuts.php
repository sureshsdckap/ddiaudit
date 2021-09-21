<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddShortcuts implements ObserverInterface
{
    /**
     * @var $shortcutBlockClass
     */
    private $shortcutBlockClass;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface
     */
    protected $gatewayConfig;
    
    /**
     * AddShortcuts Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface $gatewayConfig
     * @param array                                                 $blockClass
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\ButtonConfigInterface $gatewayConfig,
        $blockClass
    ) {
        $this->gatewayConfig = $gatewayConfig;
        $this->shortcutBlockClass = $blockClass;
    }

    /**
     * Main action method.
     *
     * This method executes a shortcut buttons block.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Block\ShortcutButtons $shortcutButtons */
        $shortcutButtons = $observer->getEvent()->getContainer();

        if ($observer->getEvent()->getIsCatalogProduct()) {
            if (!$this->gatewayConfig->isButtonEnabledOnProduct()) {
                return;
            }
        } else {
            if (!$this->gatewayConfig->isButtonEnabledOnCart()) {
                return;
            }
        }
        
        $shortcut = $shortcutButtons->getLayout()->createBlock($this->shortcutBlockClass);

        $shortcut->setIsCatalogProduct($observer->getEvent()->getIsCatalogProduct());

        $shortcutButtons->addShortcut($shortcut);
    }
}
