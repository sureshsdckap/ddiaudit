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

class InitializeRequestBuilder extends AbstractRequestBuilder
{

    const DUPLICATE_WINDOW_INTERVAL = 10;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var Config
     */
    protected $config;

    /**
     * InitializeRequestBuilder Constructor
     *
     * @param Reader $configReader
     * @param Config $config
     * @param SubjectReader $subjectReader
     * @param string $transactionType
     * @param UrlInterface $urlBuilder
     * @param Session $session
     */
    public function __construct(
        Reader $configReader,
        Config $config,
        SubjectReader $subjectReader,
        $transactionType,
        UrlInterface $urlBuilder,
        Session $session
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->session = $session;
        $this->config = $config;

        parent::__construct($configReader, $subjectReader, $transactionType);
    }

    /**
     * Initialize Request Builder
     *
     * Prepare Anet request with transaction type and merchant authentication data
     *
     * @param array $commandSubject
     * @return array
     * @throws \Exception
     */
    public function build(array $commandSubject)
    {
        $quote = $this->session->getQuote();
        
        $quote->getPayment()->importData(['method' => Config::CODE]);
        
        $methodInstance = $quote->getPayment()->getMethodInstance();

        $anetRequest = new AnetAPI\CreateTransactionRequest();
        $transactionRequestType = new AnetAPI\TransactionRequestType();

        $transactionRequestType
            ->setTransactionType(
                $this->getTransactionType()
            )->setPayment(
                $this->preparePayment()
            )->setAmount(
                $this->formatPrice($quote->getBaseGrandTotal())
            )->setCurrencyCode(
                $quote->getBaseCurrencyCode()
            )->addToTransactionSettings(
                $this->prepareTransactionSettings()
                //            )->setLineItems(
                //                $this->prepareLineItems($quote->getItems())
            );

        // Above no line items assigned because paypal rejects transaction without explaining a reason sometimes

        if (!($commandSubject['ignoreShippingAddress'] ?? false)) {
            if ($address = $quote->getShippingAddress()) {
                $transactionRequestType->setShipTo(
                    $this->preparePayPalAddressData($address)
                );
            }
        }

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

    /**
     * Prepare line item in Amet request
     *
     * @param  array $item
     * @return array $anetItem
     */
    protected function prepareLineItem($item)
    {

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $anetItem = new AnetAPI\LineItemType();

        $formattedName = substr($item->getName(), 0, 31);
        $formattedSku = substr($item->getSku(), 0, 31);
        $formattedDescription = substr($item->getDescription(), 0, 31);

        $anetItem
            ->setName($formattedName)
            ->setItemId($formattedSku)
            ->setQuantity($item->getQty())
            ->setDescription($formattedDescription)
            ->setUnitPrice($item->getBasePriceInclTax() - $item->getBaseDiscountAmount());
        return $anetItem;
    }

    /**
     * Get the payment Action type
     *
     * @return string
     * @throws \Exception
     */
    public function getTransactionType()
    {
        switch ($this->config->getPaymentAction()) {
            case PaymentAction::ACTION_AUTHORIZE:
                return AbstractClient::TRANSACTION_AUTH_ONLY;
                break;
            case PaymentAction::ACTION_AUTHORIZE_CAPTURE:
                return AbstractClient::TRANSACTION_AUTH_CAPTURE;
                break;
            default:
                throw new \Exception('unknown payment action');
        }
    }

    /**
     * Set customer address data for the PayPal request
     *
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return AnetAPI\CustomerAddressType
     */
    protected function preparePayPalAddressData($address)
    {
        $customerAddress = new AnetAPI\CustomerAddressType();

        $customerAddress->setFirstName(
            $address->getFirstname()
        )->setLastName(
            $address->getLastname()
        )->setCountry(
            $address->getCountry()
        )->setCity(
            $address->getCity()
        )->setState(
            $address->getRegion()
        )->setAddress(
            $address->getStreetFull()
        )->setZip(
            $address->getPostcode()
        )->setCompany(
            $address->getCompany()
        );

        return $customerAddress;
    }

    /**
     * Set the return URL for PayPal Request
     *
     * @return AnetAPI\PaymentType
     */
    protected function preparePayment()
    {
        $anetPayment = new AnetAPI\PaymentType();
        $anetPayPalType = new AnetAPI\PayPalType();

        $returnUrl = $this->urlBuilder->getUrl('anet_paypal_express/checkout/review');

        $anetPayPalType->setSuccessUrl(
            $returnUrl
        )->setCancelUrl(
            $returnUrl
        );

        $anetPayment->setPayPal($anetPayPalType);

        return $anetPayment;
    }
}
