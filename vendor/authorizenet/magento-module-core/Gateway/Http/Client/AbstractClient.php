<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Http\Client;

use AuthorizeNet\Core\Model\Logger;
use AuthorizeNet\Core\Gateway\Config\Config;
use AuthorizeNet\Core\Service\AnetRequestProxy;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use AuthorizeNet\Core\Service\AnetRequestProxyFactory;
use AuthorizeNet\Core\Service\AnetRequestSerializerInterface;

use net\authorize\api\contract\v1 as AnetAPI;
use net\authorize\api\constants\ANetEnvironment as ANetEnvironment;

abstract class AbstractClient implements ClientInterface
{
    const TRANSACTION_DUMMY = 'dummy';
    const TRANSACTION_AUTH_CAPTURE = 'authCaptureTransaction';
    const TRANSACTION_AUTH_ONLY = 'authOnlyTransaction';
    const TRANSACTION_REFUND = 'refundTransaction';
    const TRANSACTION_VOID = 'voidTransaction';
    const TRANSACTION_GET_DETAILS = 'getDetailsTransaction';
    const TRANSACTION_AUTH_CAPTURE_CONTINUE = 'authCaptureContinueTransaction';
    const TRANSACTION_AUTH_ONLY_CONTINUE = 'authOnlyContinueTransaction';
    const TRANSACTION_PRIOR_AUTH_CAPTURE = 'priorAuthCaptureTransaction';

    const VC_DATA_DESCRIPTOR = 'COMMON.VCO.ONLINE.PAYMENT';

    /**
     * @var AnetRequestProxyFactory
     */
    protected $anetRequestProxyFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \AuthorizeNet\Core\Service\AnetRequestSerializerInterface
     */
    protected $serializer;

    /**
     * AbstractClient Constructor
     *
     * @param Logger $logger
     * @param Config $config
     * @param AnetRequestSerializerInterface $serializer
     * @param AnetRequestProxyFactory $anetRequestProxyFactory
     */
    public function __construct(
        Logger $logger,
        Config $config,
        AnetRequestSerializerInterface $serializer,
        AnetRequestProxyFactory $anetRequestProxyFactory
    ) {
        $this->anetRequestProxyFactory = $anetRequestProxyFactory;
        $this->logger = $logger;
        $this->config = $config;
        $this->serializer = $serializer;
    }

    /**
     * Send request
     *
     * @param TransferInterface $transferObject
     * @return array
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $body = $transferObject->getBody();

        /** @var AnetAPI\ANetApiRequestType $request */
        $request = $body['request'];
        $response = false;

        try {
            $response = $this->process($request);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw $e;
        } finally {

            $this->logger->debug(
                [
                    'request' => $this->serializer->toArray($request),
                    'response' => $this->serializer->toArray($response),
                ]
            );
        }

        return [$response];
    }

    /**
     * Operate in order using data which came from Authorize.Net.
     *
     * @param $request
     * @return AnetAPI\AnetApiResponseType
     */
    abstract public function process($request);

    /**
     * Operate AuthorizeNet Request
     *
     * This method executes to operate anet request.
     *
     * @param $request
     * @param string $type
     * @return AnetRequestProxy
     */
    protected function prepareAnetRequest($request, $type)
    {
        return $this->anetRequestProxyFactory->create()
            ->setRequest($request)
            ->setRequestType($type);
    }

    /**
     * Get endpoint URL as per configured mode
     *
     * @return string
     */
    protected function getEndpointUrl()
    {
        return $this->config->isTestMode() ? ANetEnvironment::SANDBOX : ANetEnvironment::PRODUCTION;
    }
}
