<?php

namespace AuthorizeNet\Core\Model\Logger;

class Censor
{

    const CENSOR_MASK = '**MASKED**';
    const CENSOR_MASK_PAN = 'xxxx';
    const CENSOR_MASK_OBJECT = '**MASKED_OBJECT**';

    private $censorMap = [
        'cardCode' => ['replacement' => self::CENSOR_MASK],
        'cardNumber' => ['replacement' => self::CENSOR_MASK],
        "expirationDate" => ['replacement' => self::CENSOR_MASK],
        "accountNumber" => ['replacement' => self::CENSOR_MASK],
        "nameOnAccount" => ['replacement' => self::CENSOR_MASK],
        "transactionKey" => ['replacement' => self::CENSOR_MASK],
        "dataValue" => ['replacement' => self::CENSOR_MASK], // to eliminate possible logging of encrypted VCO payment data
        "dataKey" => ['replacement' => self::CENSOR_MASK],
    ];

    /*
     * Regexps from anet SDK
     */
    private $PANRegexps = [
        '/4\\p{N}{3}([\\ \\-]?)\\p{N}{4}\\1\\p{N}{4}\\1\\p{N}{4}/u',
        '/4\\p{N}{3}([\\ \\-]?)(?:\\p{N}{4}\\1){2}\\p{N}(?:\\p{N}{3})?/u',
        '/5[1-5]\\p{N}{2}([\\ \\-]?)\\p{N}{4}\\1\\p{N}{4}\\1\\p{N}{4}/u',
        '/6(?:011|22(?:1(?=[\\ \\-]?(?:2[6-9]|[3-9]))|[2-8]|9(?=[\\ \\-]?(?:[01]|2[0-5])))|4[4-9]\\p{N}|5\\p{N}\\p{N})([\\ \\-]?)\\p{N}{4}\\1\\p{N}{4}\\1\\p{N}{4}/u',
        '/35(?:2[89]|[3-8]\\p{N})([\\ \\-]?)\\p{N}{4}\\1\\p{N}{4}\\1\\p{N}{4}/u',
        '/3[47]\\p{N}\\p{N}([\\ \\-]?)\\p{N}{6}\\1\\p{N}{5}/u',
    ];


    /**
     * Method to strip sensitive data from input
     *
     * 1. Strips data from anet requests/responses like transactionKey etc.
     * 2. Strips all data that looks like PAN in input strings
     * 3. Replaces all input objects and other types with string mask because there is not way to determine how to clear them.
     *
     * @param $value mixed
     * @return mixed
     */
    public function censorSensitiveData($value)
    {

        if (is_array($value)) {
            array_walk_recursive($value, [$this, 'censorArrayItem']);
            return $value;
        }

        if (is_string($value)) {
            return $this->censorString($value);
        }

        //we have no idea how to clear other types of data so returning empty string
        return '';
    }

    /**
     * Processes single array item
     *
     * @param $value
     * @param $index
     */
    private function censorArrayItem(&$value, $index)
    {
        if (isset($this->censorMap[$index])) {
            $value = $this->censorMap[$index]['replacement'];
            return;
        };

        if (is_string($value)) {
            $value = $this->censorString($value);
            return;
        }

        // we don't know anything about internal structure so just masking that
        if (is_object($value)) {
            $value = static::CENSOR_MASK_OBJECT;
            return;
        }

        $value = '';
    }

    /**
     * Processes string inputs
     *
     * @param $value
     * @return null|string|string[]
     */
    private function censorString($value)
    {
        $value = preg_replace($this->PANRegexps, self::CENSOR_MASK_PAN, $value);

        if ($value == null) {
            $value = '';
        }

        return $value;
    }
}
