<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Command;

use AuthorizeNet\Core\Service\AnetRequestSerializerInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use net\authorize\api\contract\v1\GetTransactionDetailsResponse;

class GetTransactionDetailsCommand implements \Magento\Payment\Gateway\CommandInterface
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
     * @var AnetRequestSerializerInterface
     */
    private $serializer;

    /**
     * GetTransactionDetailsCommand Constructor
     *
     * @param TransferFactoryInterface                     $transferFactory
     * @param BuilderInterface                             $requestBuilder
     * @param ClientInterface                              $client
     * @param ValidatorInterface                           $validator
     * @param AnetRequestSerializerInterface               $serializer
     */
    public function __construct(
        TransferFactoryInterface $transferFactory,
        BuilderInterface $requestBuilder,
        ClientInterface $client,
        ValidatorInterface $validator,
        AnetRequestSerializerInterface $serializer
    ) {
        $this->transferFactory = $transferFactory;
        $this->requestBuilder = $requestBuilder;
        $this->transactionClient = $client;
        $this->responseValidator = $validator;
        $this->serializer = $serializer;
    }

    /**
     * Get the transaction details.
     *
     * @param array $commandSubject
     * @return \net\authorize\api\contract\v1\TransactionDetailsType|array
     * @throws CommandException
     */
    public function execute(array $commandSubject)
    {
        $transferObject = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        try {
            $response = $this->transactionClient->placeRequest($transferObject);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new CommandException(__('Unable to get transaction details'));
        }

        $validationResult = $this->responseValidator->validate(['response' => $response]);
        if (!$validationResult->isValid()) {
            throw new CommandException(__('Unable to get transaction details'));
        }

        /** @var GetTransactionDetailsResponse $anetResponse */
        $anetResponse = array_shift($response);

        if ($commandSubject['resultAsObject'] ?? false) {
            return $anetResponse->getTransaction();
        }

        return $this->serializer->toArray($anetResponse->getTransaction());
    }
}
