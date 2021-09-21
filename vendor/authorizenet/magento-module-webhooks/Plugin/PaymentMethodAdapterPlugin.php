<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Plugin;

class PaymentMethodAdapterPlugin
{
    protected $methods;

    public function __construct(
        $methods = []
    ) {
    
        $this->methods = $methods;
    }

    /**
     * Process for Refund payment
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param callable $proceed
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return \Magento\Payment\Model\Method\Adapter
     */
    public function aroundRefund(
        \Magento\Payment\Model\Method\Adapter $subject,
        callable $proceed,
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
    
        if (!$this->skipGatewayCommand($payment)) {
            $proceed($payment, $amount);
        }
        return $subject;
    }

    /**
     * Process for Capture payment
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param callable $proceed
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param $amount
     * @return \Magento\Payment\Model\Method\Adapter
     */
    public function aroundCapture(
        \Magento\Payment\Model\Method\Adapter $subject,
        callable $proceed,
        \Magento\Payment\Model\InfoInterface $payment,
        $amount
    ) {
    
        if (!$this->skipGatewayCommand($payment)) {
            $proceed($payment, $amount);
        }
        return $subject;
    }

    /**
     * Process for void payment
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param callable $proceed
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Magento\Payment\Model\Method\Adapter
     */
    public function aroundVoid(
        \Magento\Payment\Model\Method\Adapter $subject,
        callable $proceed,
        \Magento\Payment\Model\InfoInterface $payment
    ) {
    
        if (!$this->skipGatewayCommand($payment)) {
            $proceed($payment);
        }
        return $subject;
    }

    /**
     * Process for Accept payment
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param callable $proceed
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Magento\Payment\Model\Method\Adapter
     */
    public function aroundAcceptPayment(
        \Magento\Payment\Model\Method\Adapter $subject,
        callable $proceed,
        \Magento\Payment\Model\InfoInterface $payment
    ) {
    
        if (!$this->skipGatewayCommand($payment)) {
            $proceed($payment);
        }
        return $subject;
    }

    /**
     * Process for Deny payment
     *
     * @param \Magento\Payment\Model\Method\Adapter $subject
     * @param callable $proceed
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Magento\Payment\Model\Method\Adapter
     */
    public function aroundDenyPayment(
        \Magento\Payment\Model\Method\Adapter $subject,
        callable $proceed,
        \Magento\Payment\Model\InfoInterface $payment
    ) {
    
        if (!$this->skipGatewayCommand($payment)) {
            $proceed($payment);
        }
        return $subject;
    }

    /**
     * Check for skip gateway command or not
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    protected function skipGatewayCommand($payment)
    {
        if (in_array($payment->getMethod(), $this->methods) && $payment->getSkipGatewayCommand()) {
            return true;
        }
    }
}
