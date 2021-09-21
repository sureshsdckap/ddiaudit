<?php
/**
 *
 */

namespace AuthorizeNet\Core\Test\Unit\Model\Logger;

use AuthorizeNet\Core\Model\Logger\Censor;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CensorTest extends TestCase
{

    /**
     * @var Censor
     */
    protected $censor;

    protected function setUp()
    {
        $this->censor = new Censor();
    }

    /**
     * @param $input
     * @param $expected
     * @dataProvider dataProviderSensitiveKeys
     * @dataProvider dataProviderPANStrings
     * @dataProvider dataProviderOtherTypes
     */
    public function testCensorSensitiveData($input, $expected)
    {
        static::assertEquals($expected, $this->censor->censorSensitiveData($input));
    }

    public function dataProviderSensitiveKeys()
    {
        return [
            [
                'input' => [[['cardCode' => 'some sensitive data']]],
                'expected' => [[['cardCode' => '**MASKED**']]],
            ],
            [
                'input' => [[['cardNumber' => 'some sensitive data']]],
                'expected' => [[['cardNumber' => '**MASKED**']]],
            ],
            [
                'input' => [[['accountNumber' => 'some sensitive data']]],
                'expected' => [[['accountNumber' => '**MASKED**']]],
            ],
            [
                'input' => [[['nameOnAccount' => 'some sensitive data']]],
                'expected' => [[['nameOnAccount' => '**MASKED**']]],
            ],
            [
                'input' => [[['expirationDate' => 'some sensitive data']]],
                'expected' => [[['expirationDate' => '**MASKED**']]],
            ],
            [
                'input' => [[['transactionKey' => 'some sensitive data']]],
                'expected' => [[['transactionKey' => '**MASKED**']]],
            ],
            [
                'input' => [[['dataValue' => 'some sensitive data']]],
                'expected' => [[['dataValue' => '**MASKED**']]],
            ],
            [
                'input' => [[['dataKey' => '5424 0000 0000 0015']]],
                'expected' => [[['dataKey' => '**MASKED**']]],
            ],
        ];
    }

    public function dataProviderPANStrings()
    {
        return [
            [
                'input' => '   4111111111111111   ', //test in the middle of string
                'expected' => '   xxxx   ',
            ],
            [
                'input' => ['   4111111111111111   '], //test in the middle of string in array
                'expected' => ['   xxxx   '],
            ],
            [
                'input' => ['4111-1111-1111-1111'], //test in dashed format
                'expected' => ['xxxx'],
            ],
            [
                'input' => ['4111 1111 1111 1111'], //test in spaced format
                'expected' => ['xxxx'],
            ],
            [
                'input' => ['370000000000002'], //test amex
                'expected' => ['xxxx'],
            ],
            [
                'input' => ['6011000000000012'], //test diners
                'expected' => ['xxxx'],
            ],
            [
                'input' => ['5424 0000 0000 0015'], //test mastercard
                'expected' => ['xxxx'],
            ],
        ];
    }

    public function dataProviderOtherTypes()
    {
        return [
            [
                'input' => [[['someKey' => new \stdClass()]]],
                'expected' => [[['someKey' => '**MASKED_OBJECT**']]],
            ],
            [
                'input' => [[['someKey' => 1]]],
                'expected' => [[['someKey' => '']]],
            ],
            [
                'input' => new \stdClass(),
                'expected' => '',
            ],
        ];
    }
}
