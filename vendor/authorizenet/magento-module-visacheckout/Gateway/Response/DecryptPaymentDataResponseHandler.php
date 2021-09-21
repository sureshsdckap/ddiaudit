<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;

class DecryptPaymentDataResponseHandler implements HandlerInterface
{

    const DATA_KEY_DECRYPTED_DATA = 'visaDecryptedData';
    const DATA_KEY_BILLING_INFO = 'billInfo';
    const DATA_KEY_SHIPPING_INFO = 'shipInfo';
    const DATA_KEY_PAYMENT_INFO = 'paymentInfo';
    const DATA_KEY_CARD_INFO = 'cardInfo';
    
    /**
     * @var $subjectReader
     */
    protected $subjectReader;

    /**
     * DecryptPaymentDataResponseHandler Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Handle response
     *
     * Update additional payment information and set last 4 digits of CC.
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);

        /** @var \net\authorize\api\contract\v1\DecryptPaymentDataResponse $responseObject */
        $responseObject = $this->subjectReader->readResponseObject(['response' => $response]);
        
        $payment = $paymentDO->getPayment();
        
        $cardInfo = $responseObject->getCardInfo();

        $payment->setAdditionalInformation(self::DATA_KEY_DECRYPTED_DATA, [
            self::DATA_KEY_BILLING_INFO => $responseObject->getBillingInfo(),
            self::DATA_KEY_SHIPPING_INFO => $responseObject->getShippingInfo(),
            self::DATA_KEY_PAYMENT_INFO => $responseObject->getPaymentDetails(),
            self::DATA_KEY_CARD_INFO => $cardInfo,
        ]);

        if ($payment instanceof \Magento\Sales\Model\Order\Payment || $payment instanceof \Magento\Quote\Model\Quote\Payment
        ) {
            $payment->setCcLast4(substr($cardInfo->getCardNumber(), -4, 4));
        }
    }
}
