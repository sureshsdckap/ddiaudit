<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Command;

use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Command\Result\ArrayResult;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;

use net\authorize\api\contract\v1\TransactionResponseType;

class GetDetailsCommand implements \Magento\Payment\Gateway\CommandInterface
{
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
     * GetDetailsCommand Constructor
     *
     * @param TransferFactoryInterface $transferFactory
     * @param BuilderInterface $requestBuilder
     * @param ClientInterface $transactionClient
     * @param ValidatorInterface $responseValidator
     */
    public function __construct(
        TransferFactoryInterface $transferFactory,
        BuilderInterface $requestBuilder,
        ClientInterface $transactionClient,
        ValidatorInterface $responseValidator
    ) {
        $this->transferFactory = $transferFactory;
        $this->requestBuilder = $requestBuilder;
        $this->transactionClient = $transactionClient;
        $this->responseValidator = $responseValidator;
    }

    /**
     * Manage the response and update the transaction info in Anet
     *
     * @param array $commandSubject
     * @return array TransactionResponseType
     * @throws \Magento\Payment\Gateway\Command\CommandException
     */
    public function execute(array $commandSubject)
    {
        $transferObject = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->transactionClient->placeRequest($transferObject);

        $validationResult = $this->responseValidator->validate(['response' => $response]);
        if (! $validationResult->isValid()) {
            throw new \Magento\Payment\Gateway\Command\CommandException(__('Unable to get transaction details'));
        }

        $anetResponse = array_shift($response);
        $transactionDetails = $anetResponse->getTransactionResponse();

        return $transactionDetails;
    }
}
