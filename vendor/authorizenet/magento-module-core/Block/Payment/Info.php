<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Block\Payment;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

class Info extends ConfigurableInfo
{

    /**
     * Get the payment method label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Prepare view of Payment method fields
     *
     * @param string $field
     * @param string $value
     * @return Phrase
     */
    protected function getValueView($field, $value)
    {

        if (is_array($value)) {
            $value = $this->serializeArrayRecursive($value);
        }

        return parent::getValueView($field, $value);
    }

    /**
     * To serialize the Recursive Array
     *
     * @param value
     * @param separator
     * @param lineSeparator
     * @return String
     */
    private function serializeArrayRecursive($value, $separator = ': ', $lineSeparator = "\n")
    {

        $out = [];

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $item = $this->serializeArrayRecursive($item, $separator, $lineSeparator);
            }

            $line = [];

            if (is_string($key)) {
                $line[] = ucfirst($key);
            }

            $line[] = $item;

            $out[] = implode($separator, $line);
        }

        return implode($lineSeparator, $out);
    }
}
