<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

class VoidHandler extends TransactionIdHandler
{
    /**
     * Check that Can close parent transaction or not.
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    protected function canCloseParentTransaction($payment)
    {
        return true;
    }

    /**
     * Check transaction
     *
     * Check that can close a transaction or not.
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    protected function canCloseTransaction($payment)
    {
        return true;
    }
}
