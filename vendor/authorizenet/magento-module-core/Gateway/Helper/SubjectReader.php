<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Helper;

use Magento\Payment\Gateway\Helper;
use Magento\Vault\Model\Ui\VaultConfigProvider;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

use net\authorize\api\contract\v1 as AnetAPI;

class SubjectReader
{
    /**
     * Reads AuthorizeNet payment subject
     *
     * @param array $subject
     * @return PaymentDataObjectInterface
     */
    public function readPayment(array $subject)
    {
        return Helper\SubjectReader::readPayment($subject);
    }

    /**
     * Reads amount from a subject
     *
     * @param array $subject
     * @return mixed
     */
    public function readAmount(array $subject)
    {
        return Helper\SubjectReader::readAmount($subject);
    }

    /**
     * Retrieve the Opaque data from Payment Subject
     *
     * @param array $subject
     * @return string
     */
    public function readOpaqueData(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['opaque_data']) || !is_string($info['opaque_data'])) {
            throw new \InvalidArgumentException('Opaque data does not exist');
        }
        return $info['opaque_data'];
    }

    /**
     * Retrieve payment information of ECheck and set info of routing number
     *
     * @param array $subject
     * @return string
     */
    public function readECheckRoutingNumber(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['routingNumber']) || !is_string($info['routingNumber'])) {
            throw new \InvalidArgumentException('ECheck routing number does not exist.');
        }
        return $info['routingNumber'];
    }

    /**
     * Retrieve ECheck Account Number and set info on Account Number.
     *
     * @param array $subject
     * @return string
     */
    public function readECheckAccountNumber(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['accountNumber']) || !is_string($info['accountNumber'])) {
            throw new \InvalidArgumentException('ECheck account number does not exist.');
        }
        return $info['accountNumber'];
    }

    /**
     * Retrieve payment details and set account name.
     *
     * @param array $subject
     * @return string
     */
    public function readECheckNameOnAccount(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['accountName']) || !is_string($info['accountName'])) {
            throw new \InvalidArgumentException('ECheck account name does not exist.');
        }
        return $info['accountName'];
    }

    /**
     * Retrieve Payment Details and set account type.
     *
     * @param array $subject
     * @return string
     */
    public function readECheckAccountType(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['accountType']) || !is_string($info['accountType'])) {
            throw new \InvalidArgumentException('ECheck account type does not exist.');
        }
        return $info['accountType'];
    }

    /**
     * Retrieve Payment Details and set PayPal Initial Transaction Id.
     *
     * @param array $subject
     * @return string
     */
    public function readPayPalInitTransId(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['initTransId']) || !is_string($info['initTransId'])) {
            throw new \LogicException('PayPal Initial Transaction Id is not provided.');
        }
        return $info['initTransId'];
    }

    /**
     * Retrieve Payment Details and set PayPal PayerId.
     *
     * @param array $subject
     * @return string
     */
    public function readPayPalPayerId(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();

        $info = (array)$payment->getAdditionalInformation();
        if (!isset($info['payerId']) || !is_string($info['payerId'])) {
            throw new \InvalidArgumentException('PayPal PayerId does not exist.');
        }
        return $info['payerId'];
    }

    /**
     * Retrieve response data.
     *
     * @param array $subject
     * @return AnetAPI\AnetApiResponseType
     */
    public function readResponseObject(array $subject)
    {
        if (!isset($subject['response'][0])
            || !$subject['response'][0] instanceof AnetAPI\AnetApiResponseType
        ) {
            throw new \InvalidArgumentException('Response data object should be provided');
        }

        return $subject['response'][0];
    }

    /**
     * Retrieve transaction response object.
     *
     * @param array $subject
     * @return AnetAPI\CreateTransactionResponse
     */
    public function readTransactionResponseObject(array $subject)
    {
        $response = $this->readResponseObject(['response' => $subject]);
        
        if (!$response instanceof AnetAPI\CreateTransactionResponse) {
            throw new \InvalidArgumentException('Response data object type is invalid');
        }
        
        return $response;
    }

    /**
     * Retrieve customer profile response
     *
     * @param array $subject
     * @return AnetAPI\CreateCustomerProfileResponse
     */
    public function readCreateCustomerProfileResponseObject(array $subject)
    {
        $response = $this->readResponseObject(['response' => $subject]);

        if (!$response instanceof AnetAPI\CreateCustomerProfileResponse) {
            throw new \InvalidArgumentException('Response data object type is invalid');
        }

        return $response;
    }

    /**
     * Retrieve token status
     *
     * @param array $subject
     * @return bool
     */
    public function readIsTokenEnabled(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();
        $info = (array)$payment->getAdditionalInformation();

        return isset($info[VaultConfigProvider::IS_ACTIVE_CODE]) && $info[VaultConfigProvider::IS_ACTIVE_CODE];
    }

    /**
     * Retrieve payment details and set public hash.
     *
     * @param array $subject
     * @return string
     */
    public function readPublicHash(array $subject)
    {
        $payment = $this->readPayment($subject)->getPayment();
        $info = (array)$payment->getAdditionalInformation();

        if (empty($info['public_hash']) || !is_string($info['public_hash'])) {
            throw new \InvalidArgumentException('Public Hash should be provided');
        }

        return $info['public_hash'];
    }

    /**
     * Retrieve Transaction id.
     *
     * @param array $subject
     * @return int
     */
    public function readTransactionId(array $subject)
    {
        if (!isset($subject['transactionId']) || !$subject['transactionId']) {
            throw new \InvalidArgumentException('Transaction id does not exist');
        }
        return $subject['transactionId'];
    }

    /**
     * Retrieve Login id.
     *
     * @param array $subject
     * @return int
     */
    public function readLoginId(array $subject)
    {
        if (!isset($subject['loginId']) || !$subject['loginId']) {
            throw new \InvalidArgumentException('Login id does not exist');
        }
        return $subject['loginId'];
    }

    /**
     * Retrieve transaction key.
     *
     * @param array $subject
     * @return int
     */
    public function readTransactionKey(array $subject)
    {
        if (!isset($subject['transactionKey']) || !$subject['transactionKey']) {
            throw new \InvalidArgumentException('Transaction key does not exist');
        }
        return $subject['transactionKey'];
    }
}
