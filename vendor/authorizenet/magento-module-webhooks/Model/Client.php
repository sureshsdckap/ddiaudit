<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Model;

class Client
{
    const SANDBOX = "https://apitest.authorize.net/rest/v1";
    const PRODUCTION = "https://api.authorize.net/rest/v1";
    const API_PATH_WEBHOOK = "/webhooks";

    /**
     * @var Config
     */
    protected $config;
    /**
     * @var \Zend\Http\HeadersFactory
     */
    protected $headersFactory;
    /**
     * @var \Zend\Http\RequestFactory
     */
    protected $requestFactory;
    /**
     * @var \Zend\Stdlib\ParametersFactory
     */
    protected $parametersFactory;
    /**
     * @var \Zend\Http\ClientFactory
     */
    protected $clientFactory;


    /**
     * Client constructor
     *
     * @param \AuthorizeNet\Webhooks\Model\Config $config
     * @param \Zend\Http\HeadersFactory $headersFactory
     * @param \Zend\Http\RequestFactory $requestFactory
     * @param \Zend\Stdlib\ParametersFactory $parametersFactory
     * @param \Zend\Http\ClientFactory $clientFactory
     */
    public function __construct(
        \AuthorizeNet\Webhooks\Model\Config $config,
        \Zend\Http\HeadersFactory $headersFactory,
        \Zend\Http\RequestFactory $requestFactory,
        \Zend\Stdlib\ParametersFactory $parametersFactory,
        \Zend\Http\ClientFactory $clientFactory
    ) {
    
        $this->config = $config;
        $this->headersFactory = $headersFactory;
        $this->requestFactory = $requestFactory;
        $this->parametersFactory = $parametersFactory;
        $this->clientFactory = $clientFactory;
    }

    /**
     * API endpoint getter
     *
     * @return string
     */
    protected function getApiEndpoint()
    {
        $host = $this->config->isTestMode() ? self::SANDBOX : self::PRODUCTION;
        return $host . self::API_PATH_WEBHOOK;
    }

    /**
     * Execute the get request of endpoint
     *
     * @param string $endpoint
     * @return array
     */
    public function get($endpoint = '')
    {
        return $this->execute('get', $endpoint);
    }

    /**
     * Execute the post request of endpoint
     *
     * @param $content
     * @param string $endpoint
     * @return array
     */
    public function post($content, $endpoint = '')
    {
        return $this->execute('post', $endpoint, $content);
    }

    /**
     * Execute the delete request of endpoint
     *
     * @param $endpoint
     * @return array
     */
    public function delete($endpoint)
    {
        return $this->execute('delete', $endpoint);
    }

    /**
     * Execute the put request of endpoint
     *
     * @param $content
     * @param $endpoint
     * @return array
     */
    public function put($content, $endpoint)
    {
        return $this->execute('put', $endpoint, $content);
    }

    /**
     * Main action method.
     *
     * Execute the endpoint request and retrieve the result
     *
     * @param $method
     * @param $endpoint
     * @param null $content
     * @return array
     */
    protected function execute($method, $endpoint, $content = null)
    {
        $loginId = $this->config->getLoginId();
        $transactionKey = $this->config->getTransKey();
        $token = base64_encode($loginId . ':' . $transactionKey);

        $httpHeaders = $this->headersFactory->create();
        $httpHeaders->addHeaders([
            'Authorization' => 'Basic ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]);
        $request = $this->requestFactory->create();
        $request->setHeaders($httpHeaders);
        $request->setUri($this->getApiEndpoint() . '/' . $endpoint);
        $request->setMethod($method);

        $params = $this->parametersFactory->create(['searchCriteria' => '*']);
        $request->setQuery($params);
        $request->setContent($content);

        $client = $this->clientFactory->create();
        $options = [
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            'maxredirects' => 0,
            'timeout' => 30
        ];
        $client->setOptions($options);
        $response = $client->send($request);
        $result = [
            'status' => $response->getStatusCode(),
            'data' => json_decode($response->getContent())
        ];
        return $result;
    }
}
