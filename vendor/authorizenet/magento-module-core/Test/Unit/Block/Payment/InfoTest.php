<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Block\Payment;

use PHPUnit\Framework\TestCase;

class InfoTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Test\Unit\Block\Payment\InfoTestHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $infoBlock;

    protected function setUp()
    {
        $this->infoBlock = new \AuthorizeNet\Core\Test\Unit\Block\Payment\InfoTestHelper();
    }
    
    public function testGetLabel()
    {
        
        $field = 'someLabel';
        
        $label = $this->infoBlock->___getLabel($field);
        
        static::assertInstanceOf(\Magento\Framework\Phrase::class, $label);
        static::assertEquals($field, $label->getText());
    }

    /**
     * @param $field
     * @param $value
     * @param $expected
     * @dataProvider dataProviderTestGetValueView
     */
    public function testGetValueView($field, $value, $expected)
    {
        static::assertEquals($expected, $this->infoBlock->___getValueView($field, $value));
    }

    public function dataProviderTestGetValueView()
    {
        return [
            [
                'field' => 'some_field',
                'value' => 'some_value',
                'expected' => 'some_value',
            ],
            [
                'field' => 'some_field',
                'value' => [
                    'key1' => ['innerKey' => 'value'],
                    'key2' => ['innerKey' => 'value']
                ],
                'expected' => "Key1: InnerKey: value\nKey2: InnerKey: value",
            ],
        ];
    }
}
