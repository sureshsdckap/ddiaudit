<?php

namespace AuthorizeNet\Webhooks\Block\Form\Element;

/**
 * Class Status
 * @package AuthorizeNet\Webhooks\Block\Form\Element\Link\Webhooks
 */
class Links extends \Magento\Framework\Data\Form\Element\AbstractElement
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
        $html = '<button type="button" onclick="window.open(\'' . $this->urlBuilder->getUrl("anet_webhooks/status") . '\',\'_self\')"><span>' . __("Webhooks Status") . '</span></button>';
        $html .= '<button type="button" onclick="window.open(\'' . $this->urlBuilder->getUrl("anet_webhooks/payload") . '\',\'_self\')"><span>' . __("Webhooks Payloads") . '</span></button>';
        return $html;
    }
}
