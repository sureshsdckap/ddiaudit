<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

class RefundHandler extends TransactionIdHandler
{
    /**
     * Check parent transaction
     *
     * Check and return value of Can close parent transaction or not.
     *
     * @param \Magento\Sales\Model\Order\Payment $payment
     * @return bool
     */
    protected function canCloseParentTransaction($payment)
    {
        return !(bool)$payment->getCreditmemo()->getInvoice()->canRefund();
    }
}
