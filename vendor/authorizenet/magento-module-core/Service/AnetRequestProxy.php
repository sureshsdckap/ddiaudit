<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Service;

use AuthorizeNet\Core\Model\Logger;
use net\authorize\api\constants\ANetEnvironment;
use net\authorize\api\contract\v1\ANetApiRequestType;
use net\authorize\api\contract\v1\ANetApiResponseType;
use net\authorize\api\controller\base\IApiOperation;
use net\authorize\util\HttpClient;

class AnetRequestProxy implements IApiOperation
{
    const TYPE_CREATE_TRANSACTION = 'net\authorize\api\contract\v1\CreateTransactionResponse';
    const TYPE_DECRYPT_PAYMENT_DATA = 'net\authorize\api\contract\v1\DecryptPaymentDataResponse';
    const TYPE_CREATE_CUSTOMER_PROFILE = 'net\authorize\api\contract\v1\CreateCustomerProfileResponse';
    const TYPE_GET_TRANSACTION_DETAILS = 'net\authorize\api\contract\v1\GetTransactionDetailsResponse';
    const TYPE_UPDATE_HELD_TRANSACTION = 'net\authorize\api\contract\v1\UpdateHeldTransactionResponse';
    const TYPE_GET_MERCHANT_DETAILS = 'net\authorize\api\contract\v1\GetMerchantDetailsResponse';

    /**
     * @var AnetApiRequestType
     */
    protected $request;

    /**
     * @var AnetApiResponseType
     */
    protected $response;

    /**
     * @var String
     */
    protected $requestType;

    /**
     * @var AnetRequestSerializerInterface
     */
    protected $serializer;

    /**
     * @var \net\authorize\util\HttpClient
     */
    protected $httpClient;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var Logger|null
     */
    protected $logger;

    /**
     * AnetRequestProxy Constructor
     *
     * @param Logger $logger
     * @param HttpClient $httpClient
     * @param AnetRequestSerializerInterface $serializer
     */
    public function __construct(
        Logger $logger,
        HttpClient $httpClient,
        AnetRequestSerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
    }

    /**
     * Get API Response
     *
     * @return ANetApiResponseType
     */
    public function getApiResponse()
    {
        return $this->response;
    }

    /**
     * Set Request
     *
     * @param ANetApiRequestType $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Set Request type
     *
     * @param string $requestType
     * @return $this
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;
        return $this;
    }

    /**
     * Execute API response
     *
     * @param string $endPoint
     * @return ANetApiResponseType
     * @throws \Exception
     */
    public function executeWithApiResponse($endPoint = ANetEnvironment::CUSTOM)
    {
        $this->execute($endPoint);
        return $this->getApiResponse();
    }

    /**
     * Main action method.
     *
     * This method executes to set client id and get a valid response from API and set HTTP client request.
     *
     * @param null|string $endPoint
     * @throws \Exception
     */
    public function execute($endPoint = ANetEnvironment::CUSTOM)
    {
        $this->request->setClientId("sdk-php-" . ANetEnvironment::VERSION);

        $request = $this->serializer->serialize($this->request);

        $this->httpClient->setPostUrl($endPoint);

        if (! $response = $this->httpClient->_sendRequest($request)) {
            throw new \Exception("Error getting valid response from api. Check log file for error details");
        }

        $this->response = $this->serializer->deserialize($response, $this->requestType);
    }
}
