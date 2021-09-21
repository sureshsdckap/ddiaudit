<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */
namespace AuthorizeNet\Core\Gateway\Request;

use AuthorizeNet\Core\Gateway\Response\TransactionIdHandler;
use net\authorize\api\contract\v1 as AnetAPI;

class UpdateHeldTransactionRequest extends AbstractRequestBuilder
{
    
    const ACTION_APPROVE = 'approve';
    const ACTION_DECLINE = 'decline';

    /**
     * @var $actionType
     */
    protected $actionType;
    
    /**
     * UpdateHeldTransactionRequest Constructor
     *
     * @param  \AuthorizeNet\Core\Gateway\Config\Reader $reader
     * @param  \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param  string $transactionTyp
     * @param  string $actionType
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Reader $reader,
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader,
        string $transactionType = '',
        string $actionType = self::ACTION_APPROVE
    ) {
        
        $this->actionType = $actionType;
        
        parent::__construct($reader, $subjectReader, $transactionType);
    }

    /**
     * Build request to Update Held Transaction
     *
     * @param array $commandSubject
     * @return array $request
     */
    public function build(array $commandSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($commandSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $anetRequest = new AnetAPI\UpdateHeldTransactionRequest();
        $heldRequest = new AnetAPI\HeldTransactionRequestType();
        
        $heldRequest
            ->setRefTransId($payment->getAdditionalInformation(TransactionIdHandler::TRANSACTION_ID))
            ->setAction($this->actionType);

        $anetRequest
            ->setHeldTransactionRequest(
                $heldRequest
            )->setMerchantAuthentication(
                $this->prepareMerchantAuthentication($payment->getMethodInstance())
            );

        return ['request' => $anetRequest];
    }
}
