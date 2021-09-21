<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class AccountType implements ArrayInterface
{
    /**
     * Set the account type options in array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'checking', 'label' => __('Checking')],
            ['value' => 'savings', 'label' => __('Savings')]
        ];
    }
}
