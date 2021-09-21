<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Request;

use net\authorize\api\contract\v1 as AnetAPI;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order\Payment;

abstract class AbstractRequestBuilder implements \Magento\Payment\Gateway\Request\BuilderInterface
{
    use Formatter;

    const DUPLICATE_WINDOW_INTERVAL = 60;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader
     */
    protected $configReader;

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader
     */
    protected $subjectReader;

    /**
     * @var string
     */
    protected $transactionType;

    /**
     * AbstractRequestBuilder Constructor
     *
     * @param \AuthorizeNet\Core\Gateway\Config\Reader $configReader
     * @param \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader
     * @param string $transactionType
     */
    public function __construct(
        \AuthorizeNet\Core\Gateway\Config\Reader $configReader,
        \AuthorizeNet\Core\Gateway\Helper\SubjectReader $subjectReader,
        $transactionType
    ) {
        $this->configReader = $configReader;
        $this->subjectReader = $subjectReader;
        $this->transactionType = $transactionType;
    }

    /**
     * Prepare request
     *
     * @param array $commandSubject
     * @return array
     */
    abstract public function build(array $commandSubject);

    /**
     * Get Anet transaction type
     *
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Prepare request for the Merchant Authentication.
     *
     * @param \Magento\Payment\Model\MethodInterface $methodInstance
     * @return AnetAPI\MerchantAuthenticationType
     */
    protected function prepareMerchantAuthentication(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        $merchantAuthentication = new AnetAPI\MerchantAuthenticationType();
        $merchantAuthentication
            ->setName($this->configReader->getLoginId($methodInstance))
            ->setTransactionKey($this->configReader->getTransactionKey($methodInstance));

        return $merchantAuthentication;
    }

    /**
     * Prepare Payment request By Nonce
     *
     * @param string $opaqueData
     * @return AnetAPI\PaymentType
     */
    protected function preparePaymentByNonce($opaqueData)
    {
        $anetPayment = new AnetAPI\PaymentType();
        $anetOpaqueData = new AnetAPI\OpaqueDataType();

        $opaqueDO = \Zend_Json::decode($opaqueData, \Zend_Json::TYPE_OBJECT);
        
        $anetOpaqueData
            ->setDataDescriptor($opaqueDO->dataDescriptor)
            ->setDataValue($opaqueDO->dataValue);

        $anetPayment->setOpaqueData($anetOpaqueData);

        return $anetPayment;
    }

    /**
     * Prepare request for Card Holder Authentication.
     *
     * @param Payment $payment
     * @return AnetAPI\CcAuthenticationType|null
     */
    protected function prepareCardholderAuthentication($payment)
    {
        $anetCcAuthentication = new AnetAPI\CcAuthenticationType();

        if ($cavv = $payment->getAdditionalInformation('CAVV')) {
            $anetCcAuthentication->setCardholderAuthenticationValue($cavv);
        }

        if ($ucaf = $payment->getAdditionalInformation('UCAFIndicator')) {
            $anetCcAuthentication->setCardholderAuthenticationValue($ucaf);
        }

        if ($eci = $payment->getAdditionalInformation('ECIFlag')) {
            $anetCcAuthentication->setAuthenticationIndicator($eci);
        }

        if ($cavv || $ucaf) {
            return $anetCcAuthentication;
        }

        return null;
    }

    /**
     * Add Line Items in request
     *
     * @param array $items
     * @return array
     * @codeCoverageIgnore
     */
    protected function prepareLineItems(array $items)
    {
        $anetItems = [];

        /** @var \Magento\Sales\Model\Order\Item $item */
        foreach ($items as $item) {
            if ($item->getParentItem() || $item->isDeleted()) {
                continue;
            }
            
            $anetItems[] = $this->prepareLineItem($item);
        }
        return $anetItems;
    }

    /**
     * Prepare for Line Item
     *
     * @param array $items
     * @return array
     */
    protected function prepareLineItem($item)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */

        $anetItem = new AnetAPI\LineItemType();

        $formattedName = substr($item->getName(), 0, 31);
        $formattedSku = substr($item->getSku(), 0, 31);
        $formattedDescription = substr($item->getDescription(), 0, 31);

        $unitPrice = $item->getBasePrice();
        if ($item->getBaseDiscountAmount() > 0) {
            $unitPrice -= round($item->getBaseDiscountAmount() / $item->getQtyOrdered(), 2);
        }

        $anetItem
            ->setName($formattedName)
            ->setItemId($formattedSku)
            ->setQuantity($item->getQtyOrdered())
            ->setDescription($formattedDescription)
            ->setUnitPrice($this->formatPrice($unitPrice))
            ->setTaxable($item->getBaseTaxAmount() && $item->getBaseTaxAmount() > 0);

        return $anetItem;
    }

    /**
     * Order data update invoice data
     *
     * Get an object of OrderType and set invoice number of order.
     *
     * @param string $orderNumber
     * @return AnetAPI\OrderType
     */
    protected function prepareOrderData($orderNumber)
    {
        $anetOrder = new AnetAPI\OrderType();
        return $anetOrder->setInvoiceNumber($orderNumber);
    }

    /**
     * Set customer address in object for the request
     *
     * @param \Magento\Payment\Gateway\Data\AddressAdapterInterface $address
     * @param bool $isShippingAddress
     * @return AnetAPI\CustomerAddressType|null
     */
    protected function prepareAddressData(\Magento\Payment\Gateway\Data\AddressAdapterInterface $address = null, $isShippingAddress = false)
    {
        if (! $address) {
            return null;
        }

        $customerAddress = new AnetAPI\CustomerAddressType();

        $customerAddress->setFirstName(
            $address->getFirstname()
        )->setLastName(
            $address->getLastname()
        )->setCountry(
            $address->getCountryId()
        )->setCity(
            $address->getCity()
        )->setState(
            $address->getRegionCode()
        )->setAddress(
            $this->getStreet($address)
        )->setZip(
            $address->getPostcode()
        )->setCompany(
            $address->getCompany()
        );

        if (!$isShippingAddress) {
            $customerAddress->setPhoneNumber($address->getTelephone());
        }

        return $customerAddress;
    }

    /**
     * Update Customer Data
     *
     * @param int|null $id
     * @param string $email
     * @param string $type
     * @return AnetAPI\CustomerDataType
     */
    protected function prepareCustomerData($id, $email, $type = "individual")
    {
        $customerData = new AnetAPI\CustomerDataType();

        $customerData
            ->setType($type)
            ->setId($id)
            ->setEmail($email);

        return $customerData;
    }

    /**
     * Set Solution id for the request
     *
     * @param Magento\Payment\Model\MethodInterface
     * @return AnetAPI\SolutionType|null
     */
    protected function prepareSolutionId(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        $anetSolution = new AnetAPI\SolutionType();

        if ($solutionId = $this->configReader->getSolutionId($methodInstance)) {
            return $anetSolution->setId($solutionId);
        }

        return null;
    }

    /**
     * Prepare duplicate window in request
     *
     * @return AnetAPI\SettingType
     */
    protected function prepareTransactionSettings()
    {
        $duplicateWindowSetting = new AnetAPI\SettingType();
        $duplicateWindowSetting
            ->setSettingName("duplicateWindow")
            ->setSettingValue(static::DUPLICATE_WINDOW_INTERVAL);

        return $duplicateWindowSetting;
    }

    /**
     * Get the parent transaction Id for the request
     *
     * @param string $transactionId
     * @return string
     */
    protected function prepareParentTransactionId($transactionId)
    {
        $transactionIdParts = explode('-', $transactionId);
        return array_shift($transactionIdParts);
    }

    /**
     * Get Tax amount for AnetAPI
     *
     * @param $payment
     * @return AnetAPI\ExtendedAmountType|null
     */
    protected function getTax($payment)
    {
        if ($payment instanceof Payment) {
            $anetTax = new AnetAPI\ExtendedAmountType();
            $anetTax->setAmount(
                $this->formatPrice($payment->getOrder()->getBaseTaxAmount())
            );
            return $anetTax;
        }
        return null;
    }

    /**
     * Get Shipping info for AnetAPI
     *
     * @param Payment $payment
     * @return AnetAPI\ExtendedAmountType|null
     */
    protected function getShipping($payment)
    {
        if ($payment instanceof Payment) {
            $formattedName = substr($payment->getOrder()->getShippingDescription(), 0, 31);
            $formattedDescription = substr($payment->getOrder()->getShippingDescription(), 0, 255);
            $shippingPrice = $payment->getOrder()->getBaseShippingAmount();

            if ($payment->getOrder()->getBaseShippingDiscountAmount() > 0) {
                $shippingPrice -= $payment->getOrder()->getBaseShippingDiscountAmount();
            }

            $anetShipping = new AnetAPI\ExtendedAmountType();
            $anetShipping
                ->setAmount($this->formatPrice($shippingPrice))
                ->setName($formattedName)
                ->setDescription($formattedDescription);
            return $anetShipping;
        }
        return null;
    }

    /**
     * Generate reference Id.
     *
     * @param string $incrementId
     * @return string
     */
    protected function generateRefId($incrementId)
    {
        return null;
//        return $this->getTransactionType() . '_' . $incrementId;
    }

    /**
     * Get street data
     *
     * @param $address
     * @return string
     */
    protected function getStreet($address)
    {
        return $address->getStreetLine1() . PHP_EOL . $address->getStreetLine2();
    }
}
