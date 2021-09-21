<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Request;

use AuthorizeNet\Core\Gateway\Config\Reader;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use AuthorizeNet\Core\Gateway\Http\Client\AbstractClient;
use AuthorizeNet\PayPalExpress\Gateway\Config\Config;
use AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder;
use Magento\Checkout\Model\Session;
use Magento\Framework\UrlInterface;

use AuthorizeNet\Core\Model\Source\PaymentAction;
use net\authorize\api\contract\v1 as AnetAPI;

class GetDetailsRequestBuilder extends AbstractRequestBuilder
{

    /**
     * GetDetailsRequestBuilder Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Config\Reader $configReader
     * @param \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Reader $configReader,
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
    ) {
        parent::__construct($configReader, $subjectReader, \AuthorizeNet\Core\Gateway\Http\Client\AbstractClient::TRANSACTION_GET_DETAILS);
    }

    /**
     * Generate Anet request using merchant authorized data
     *
     * @param array $commandSubject
     * @return array
     * @throws \Exception
     */
    public function build(array $commandSubject)
    {

        $paymentDO = $this->subjectReader->readPayment($commandSubject);
        $transactionId = $this->subjectReader->readPayPalInitTransId($commandSubject);

        $methodInstance = $paymentDO->getPayment()->getMethodInstance();

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $transactionRequestType
            ->setTransactionType(
                $this->transactionType
            )->setRefTransId(
                $transactionId
            );

        if ($solutionId = $this->prepareSolutionId($methodInstance)) {
            $transactionRequestType->setSolution($solutionId);
        }

        $anetRequest
            ->setTransactionRequest(
                $transactionRequestType
            )->setMerchantAuthentication(
                $this->prepareMerchantAuthentication($methodInstance)
            );

        return ['request' => $anetRequest];
    }
}
