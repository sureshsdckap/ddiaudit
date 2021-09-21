<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Model\InfoInterface;
use AuthorizeNet\Core\Gateway\Config\Config;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Framework\Exception\LocalizedException;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Vault\Model\PaymentTokenManagement;

use net\authorize\api\contract\v1 as AnetAPI;

class ProfileDetailsHandler implements HandlerInterface
{
    const KEY_CARD_TYPE = 'cardType';

    const TOKEN_DETAILS_FIELDS = [
        'accountNumber',
        'accountName',
        'cardNumber',
        'cardType',
        'cardExpYear',
        'cardExpMonth'
    ];

    /**
     * @var \AuthorizeNet\Core\Model\CcTypes
     */
    private $ccTypes;

    /**
     * @var PaymentTokenInterfaceFactory
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    private $paymentExtensionFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PaymentTokenManagement
     */
    private $paymentTokenManagement;

    /**
     * ProfileDetailsHandler Constructor
     *
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param SubjectReader $subjectReader
     * @param \AuthorizeNet\Core\Model\CcTypes $ccTypes
     */
    public function __construct(
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        PaymentTokenManagement $paymentTokenManagement,
        SubjectReader $subjectReader,
        \AuthorizeNet\Core\Model\CcTypes $ccTypes
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->subjectReader = $subjectReader;
        $this->ccTypes = $ccTypes;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readCreateCustomerProfileResponseObject($response);

        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();

        if ($paymentToken = $this->getVaultPaymentToken($payment, $transaction)) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get value of payment token
     *
     * Create token with the value of a customer, payment and gateway profile id and build a payment token and return to function.
     *
     * @param InfoInterface $payment
     * @param AnetAPI\CreateCustomerProfileResponse $transaction
     * @return PaymentTokenInterface|null
     * @throws LocalizedException
     */
    private function getVaultPaymentToken(InfoInterface $payment, AnetAPI\CreateCustomerProfileResponse $transaction)
    {
        $customerProfileId = $transaction->getCustomerProfileId();
        $paymentProfileIdList = $transaction->getCustomerPaymentProfileIdList();
        $paymentProfileId = array_shift($paymentProfileIdList);
        $gatewayLoginId = $payment->getMethodInstance()->getConfigData(Config::KEY_LOGIN_ID);

        $token = $customerProfileId . ':' . $paymentProfileId . ':' . $gatewayLoginId;

        // verifying that token is truly unique
        if (!$this->isTokenUnique($token, $payment)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);

        $paymentToken->setExpiresAt(
            $this->paymentTokenFactory instanceof CreditCardTokenFactory
            ? $this->getCardExpDate($payment)
            : strtotime('+1 year')
        );

        $paymentToken->setTokenDetails(
            $this->prepareTokenDetails($payment)
        );

        return $paymentToken;
    }

    /**
     * Get card expiration date
     *
     * @param InfoInterface $payment
     * @return string
     */
    private function getCardExpDate(InfoInterface $payment)
    {
        $expDate = new \DateTime(
            $payment->getAdditionalInformation('cardExpYear')
            . '-'
            . $payment->getAdditionalInformation('cardExpMonth')
            . '-'
            . '01'
            . ' '
            . '00:00:00',
            new \DateTimeZone('UTC')
        );
        $expDate->add(new \DateInterval('P1M'));

        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Prepar token details
     *
     * @param InfoInterface $payment
     * @return string
     */
    private function prepareTokenDetails(InfoInterface $payment)
    {
        $dataFields = [];
        $paymentInfo = (array) $payment->getAdditionalInformation();
        foreach (self::TOKEN_DETAILS_FIELDS as $fieldName) {
            if (isset($paymentInfo[$fieldName])) {
                if ($fieldName == self::KEY_CARD_TYPE) {
                    $dataFields[$fieldName] = $this->ccTypes->getMagentoType($paymentInfo[$fieldName]);
                } else {
                    $dataFields[$fieldName] = $paymentInfo[$fieldName];
                }
            }
        }

        return json_encode($dataFields);
    }


    /**
     * Get Extension Attributes
     *
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }

    /**
     * @param string $token
     * @param InfoInterface $payment
     * @return bool
     * @throws LocalizedException
     */
    private function isTokenUnique($token, InfoInterface $payment)
    {
        $customerId = $payment->getOrder()->getCustomerId();
        $methodCode = $payment->getMethodInstance()->getCode();

        return !$this->paymentTokenManagement->getByGatewayToken($token, $methodCode, $customerId);
    }
}
