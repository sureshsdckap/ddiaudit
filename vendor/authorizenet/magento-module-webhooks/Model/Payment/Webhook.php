<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model\Payment;

/**
 * Class Webhook
 * This is a kind of empty payment method, used as a substitution method for webhooks transactions data retrieval, see refund handler
 * @package AuthorizeNet\Webhooks\Model\Payment
 */
class Webhook implements \Magento\Payment\Model\MethodInterface
{

    const METHOD_CODE = 'anet_webhooks';

    /**
     * @var \AuthorizeNet\Webhooks\Model\Config
     */
    protected $config;

    /**
     * Webhook Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Config\Config $config
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Config $config
    ) {
    
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return self::METHOD_CODE;
    }

    /**
     * @inheritdoc
     */
    public function getFormBlockType()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return __('Webhook');
    }

    /**
     * @inheritdoc
     */
    public function setStore($storeId)
    {
    }

    /**
     * @inheritdoc
     */
    public function getStore()
    {
    }

    /**
     * @inheritdoc
     */
    public function canOrder()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canAuthorize()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canCapture()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canCapturePartial()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canCaptureOnce()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canRefund()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canRefundPartialPerInvoice()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canVoid()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canUseInternal()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canUseCheckout()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canEdit()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canFetchTransactionInfo()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function fetchTransactionInfo(\Magento\Payment\Model\InfoInterface $payment, $transactionId)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isGateway()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isOffline()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isInitializeNeeded()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canUseForCountry($country)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getInfoBlockType()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getInfoInstance()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setInfoInstance(\Magento\Payment\Model\InfoInterface $info)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function order(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function canReviewPayment()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function acceptPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function denyPayment(\Magento\Payment\Model\InfoInterface $payment)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData($field, $storeId = null)
    {
        return $this->config->getConfigValue($field, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isActive($storeId = null)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function initialize($paymentAction, $stateObject)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConfigPaymentAction()
    {
        return null;
    }
}
