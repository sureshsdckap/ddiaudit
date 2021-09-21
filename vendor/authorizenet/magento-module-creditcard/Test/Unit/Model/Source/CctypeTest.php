<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_CreditCard
 */

namespace AuthorizeNet\CreditCard\Test\Unit\Model\Source;

use AuthorizeNet\CreditCard\Model\Source\Cctype;
use PHPUnit\Framework\TestCase;

class CctypeTest extends TestCase
{
    /**
     * @var \Magento\Payment\Model\Config | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentConfig;

    /**
     * @var Cctype
     */
    protected $model;

    protected $optionArray = [
        ['value' => 'CUP', 'label' => 'China Union Pay'],
        ['value' => 'VI', 'label' => 'Visa'],
        ['value' => 'MC', 'label' => 'MasterCard']
    ];

    protected $configTypes = ['VI' => 'Visa', 'MC' => 'MasterCard'];

    protected $specificCardTypes = ['CUP' => 'China Union Pay'];

    protected function setUp()
    {
        $this->paymentConfig = $this->getMockBuilder(\Magento\Payment\Model\Config::class)
            ->setMethods(['getCcTypes'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Cctype($this->paymentConfig);
    }

    public function testToOptionArray()
    {
        $this->paymentConfig->expects(static::once())
            ->method('getCcTypes')
            ->willReturn($this->configTypes);

        $this->assertEquals($this->optionArray, $this->model->toOptionArray());
    }
}
