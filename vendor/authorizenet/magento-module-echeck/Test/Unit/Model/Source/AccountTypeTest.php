<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Test\Unit\Model\Source;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\ECheck\Model\Source\AccountType;
use PHPUnit\Framework\TestCase;

class AccountTypeTest extends TestCase
{
    /**
     * @var AccountType|MockObject
     */
    private $accountType;

    protected function setUp()
    {
        $this->accountType = new AccountType();
    }

    /**
     * @param string $expected
     * @dataProvider getAccountTypeDataProvider
     */
    public function testToOptionArray($expected)
    {
        static::assertEquals($expected, $this->accountType->toOptionArray());
    }

    /**
     * @return array
     */
    public function getAccountTypeDataProvider()
    {
        return [
            [
                'expected' => [
                    ['value' => 'checking', 'label' => __('Checking')],
                    ['value' => 'savings', 'label' => __('Savings')]
                ]
            ]
        ];
    }
}
