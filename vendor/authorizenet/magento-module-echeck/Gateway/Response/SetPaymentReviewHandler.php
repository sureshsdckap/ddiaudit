<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_ECheck
 */

namespace AuthorizeNet\ECheck\Gateway\Response;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;

class SetPaymentReviewHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * SetPaymentReviewHandler Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Set transaction status
     *
     * Set initial transaction with Pending status
     *
     * @param  array $handlingSubject
     * @param  array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        /** @var $payment Payment */
        $payment = $this->subjectReader->readPayment($handlingSubject)->getPayment();
        $payment->setIsTransactionPending(true);
    }
}
