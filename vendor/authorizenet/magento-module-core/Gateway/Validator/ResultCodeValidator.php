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

class ResultCodeValidator extends AbstractValidator
{
    const RESULT_CODE_SUCCESS = 'Ok';
    const RESULT_CODE_FAILURE = 'Error';

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * TransferFactory Constructor
     *
     * @param ResultInterfaceFactory $resultFactory
     * @param SubjectReader          $subjectReader
     */
    public function __construct(
        ResultInterfaceFactory $resultFactory,
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
        parent::__construct($resultFactory);
    }
    
    /**
     * Check for valide transaction
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
     * @param AnetAPI\AnetApiResponseType $response
     * @return bool
     */
    protected function isSuccessfulTransaction(AnetAPI\ANetApiResponseType $response)
    {
        return $response->getMessages()->getResultCode() === self::RESULT_CODE_SUCCESS;
    }
}
