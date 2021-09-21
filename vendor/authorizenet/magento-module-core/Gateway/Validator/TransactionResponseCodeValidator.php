<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Validator;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

use net\authorize\api\contract\v1 as AnetAPI;

class TransactionResponseCodeValidator extends AbstractValidator
{
    const TRANSACTION_CODE_APPROVED = 1;
    const TRANSACTION_CODE_DECLINED = 2;
    const TRANSACTION_CODE_ERROR = 3;
    const TRANSACTION_CODE_HELD = 4;
    const TRANSACTION_CODE_INVALID_AMOUNT = 5;

    /**
     * @var array
     */
    protected $validResponseCodes;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransactionResponseCodeValidator Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader $subjectReader
     * @param array $validResponseCodes
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader,
        array $validResponseCodes = []
    ) {
        $this->validResponseCodes = $validResponseCodes;
        $this->subjectReader = $subjectReader;
        parent::__construct($resultFactory);
    }

    /**
     * Check for valid transaction
     *
     * Check transaction is valid or not if not then throw rejected transaction message.
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $this->subjectReader->readResponseObject($validationSubject);

        if ($this->isSuccessfulTransaction($response)) {
            return $this->createResult(
                true,
                []
            );
        } else {
            return $this->createResult(
                false,
                [__('Gateway rejected the transaction.')]
            );
        }
    }

    /**
     * Check transaction status
     *
     * Check transaction is successful or not.
     *
     * @param AnetAPI\ANetApiResponseType $response
     * @return bool
     */
    protected function isSuccessfulTransaction(AnetAPI\ANetApiResponseType $response)
    {
        
        $transactionDetails = $response->getTransactionResponse();
        
        if ($transactionDetails && $transactionDetails instanceof AnetAPI\TransactionResponseType) {
            return in_array($transactionDetails->getResponseCode(), $this->validResponseCodes);
        }
        
        return false;
    }
}
