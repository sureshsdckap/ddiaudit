<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model;

class EmailSender
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var Config
     */
    protected $config;

    /**
     * EmailSender constructor.
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param Config $config
     */
    public function __construct(
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \AuthorizeNet\Webhooks\Model\Config $config
    ) {
    
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->config = $config;
    }

    /**
     * Send an email
     *
     * @param array $variables
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send(array $variables)
    {
        $this->inlineTranslation->suspend();
        try {
            $template = $this->config->getEmailTemplate();
            $recipient = $this->config->getRecipientEmail();
            $sender = $this->config->getSender();
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($template)
                ->setTemplateOptions(
                    [
                        'area' => 'adminhtml',
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                )
                ->setTemplateVars($variables)
                ->setFrom($sender)
                ->addTo($recipient)
                ->getTransport();

            $transport->sendMessage();
        } finally {
            $this->inlineTranslation->resume();
        }
    }
}
