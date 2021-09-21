<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\EmailSender;
use PHPUnit\Framework\TestCase;

class EmailSenderTest extends TestCase
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilderMock;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $inlineTranslationMock;
    /**
     * @var \AuthorizeNet\Webhooks\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var EmailSender|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $emailSender;


    protected function setUp()
    {
        $this->transportBuilderMock = $this->getMockBuilder(\Magento\Framework\Mail\Template\TransportBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->inlineTranslationMock = $this->getMockBuilder(\Magento\Framework\Translate\Inline\StateInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Webhooks\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailSender = new EmailSender(
            $this->transportBuilderMock,
            $this->inlineTranslationMock,
            $this->configMock
        );
    }

    public function testSend()
    {
        $variables = ['variable' => 'test'];
        $template = 'template.phtml';
        $recipient = 'recipient';
        $sender = 'sender';
        $options = [
            'area' => 'adminhtml',
            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
        ];

        $this->inlineTranslationMock->expects(static::once())
            ->method('suspend');

        $this->configMock->expects(static::once())
            ->method('getEmailTemplate')
            ->willReturn($template);

        $this->configMock->expects(static::once())
            ->method('getRecipientEmail')
            ->willReturn($recipient);

        $this->configMock->expects(static::once())
            ->method('getSender')
            ->willReturn($sender);

        $this->transportBuilderMock->expects(static::once())
            ->method('setTemplateIdentifier')
            ->with($template)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(static::once())
            ->method('setTemplateOptions')
            ->with($options)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(static::once())
            ->method('setTemplateVars')
            ->with($variables)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(static::once())
            ->method('setFrom')
            ->with($sender)
            ->willReturnSelf();

        $this->transportBuilderMock->expects(static::once())
            ->method('addTo')
            ->with($recipient)
            ->willReturnSelf();

        $transport = $this->getMockBuilder(\Magento\Framework\Mail\TransportInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transportBuilderMock->expects(static::once())
            ->method('getTransport')
            ->willReturn($transport);

        $transport->expects(static::once())
            ->method('sendMessage');

        $this->inlineTranslationMock->expects(static::once())
            ->method('resume');

        $this->emailSender->send($variables);
    }
}
