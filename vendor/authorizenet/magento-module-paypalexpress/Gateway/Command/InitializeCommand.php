<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

use net\authorize\api\contract\v1\CreateTransactionResponse;

class InitializeCommand implements \Magento\Payment\Gateway\CommandInterface
{

    const KEY_INIT_TRANSACTION_ID = 'initTransId';
    const KEY_PAYER_ID = 'payerId';

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var ClientInterface
     */
    private $transactionClient;

    /**
     * @var ValidatorInterface
     */
    private $responseValidator;

    /**
     * @var ArrayResultFactory
     */
    private $arrayResult;

    /**
     * InitializeCommand Constructor
     *
     * @param TransferFactoryInterface $transferFactory
     * @param BuilderInterface $requestBuilder
     * @param ClientInterface $transactionClient
     * @param ValidatorInterface $responseValidator
     * @param ArrayResultFactory $resultFactory
     */
    public function __construct(
        TransferFactoryInterface $transferFactory,
        BuilderInterface $requestBuilder,
        ClientInterface $transactionClient,
        ValidatorInterface $responseValidator,
        ArrayResultFactory $resultFactory
    ) {
        $this->transferFactory = $transferFactory;
        $this->requestBuilder = $requestBuilder;
        $this->transactionClient = $transactionClient;
        $this->responseValidator = $responseValidator;
        $this->arrayResult = $resultFactory;
    }

    /**
     * Initialize PayPal Express request
     * Validate the requested data
     *
     * @param array $commandSubject
     * @return ArrayResult
     * @throws CommandException
     * @throws \Magento\Payment\Gateway\Http\ClientException
     * @throws \Magento\Payment\Gateway\Http\ConverterException
     */
    public function execute(array $commandSubject)
    {
        $transferObject = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->transactionClient->placeRequest($transferObject);

        $validationResult = $this->responseValidator->validate(['response' => $response]);
        if (! $validationResult->isValid()) {
            throw new CommandException(__('Unable to initialize PayPal Express'));
        }

        return $this->arrayResult->create(['array' => $this->extractData($response)]);
    }

    /**
     * Extract the response and get the transaction id and token
     *
     * @param array $response
     * @return array
     */
    protected function extractData($response)
    {
        /** @var CreateTransactionResponse $anetResponse */
        $anetResponse = array_shift($response);
        $transactionDetails = $anetResponse->getTransactionResponse();
        $transactionId = $transactionDetails->getTransId();
        $url = $transactionDetails->getSecureAcceptance()->getSecureAcceptanceUrl();

        $urlParts = [];
        parse_str($url, $urlParts);

        $token = isset($urlParts['token']) ? $urlParts['token'] : '';

        return ['transId' => $transactionId, 'token' => $token];
    }
}
