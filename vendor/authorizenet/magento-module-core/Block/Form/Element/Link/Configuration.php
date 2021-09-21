<?php

namespace AuthorizeNet\Core\Block\Form\Element\Link;

/**
 * Class Configuration
 * @package AuthorizeNet\Core\Block\Form\Element\Link
 */
class Configuration extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Configuration constructor.
     *
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Model\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Return the element as HTML
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '<button class="primary" type="button" onclick="window.open(\'' . $this->urlBuilder->getUrl("anet_core/merchant/setup") . '\',\'_self\')"><span>' . __("Run Authorize.Net Configuration Wizard") . '</span></button>';
        return $html;
    }
}
