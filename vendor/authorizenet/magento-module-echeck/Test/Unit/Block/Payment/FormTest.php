<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Block\Test\Unit\Payment;

use AuthorizeNet\ECheck\Block\Payment\Form;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /**
     * @var Form
     */
    protected $form;
    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;
    /**
     * @var \AuthorizeNet\ECheck\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    public function testGetAccountTypeOptions()
    {
        $this->contextMock = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);
        $this->configMock = $this->createMock(\AuthorizeNet\ECheck\Gateway\Config\Config::class);
        $this->form = new Form(
            $this->contextMock,
            $this->configMock,
            []
        );
        $accountTypeOptions = ['label' => 'value'];
        $this->configMock->expects($this->once())
            ->method('getAccountTypeOptions')
            ->willReturn($accountTypeOptions);
        $this->assertEquals($accountTypeOptions, $this->form->getAccountTypeOptions());
    }
}
