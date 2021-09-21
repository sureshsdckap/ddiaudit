<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Command;

use AuthorizeNet\Core\Gateway\Response\ProfileDetailsHandler;
use AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use AuthorizeNet\Core\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\CommandInterface;

class CreateProfileCommand implements CommandInterface
{
    /**
     * @var TransferFactory
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $transactionClient;

    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var ArrayResultFactory
     */
    private $resultFactory;

    /**
     * @var ResultCodeValidator
     */
    private $responseValidator;

    /**
     * @var ProfileDetailsHandler
     */
    private $responseHandler;

    /**
     * CreateProfileCommand Constructor
     *
     * @param TransferFactory $transferFactory
     * @param ClientInterface $client
     * @param BuilderInterface $requestBuilder
     * @param ArrayResultFactory $resultFactory
     * @param ResultCodeValidator $validator
     * @param ProfileDetailsHandler $handler
     */
    public function __construct(
        TransferFactory $transferFactory,
        ClientInterface $client,
        BuilderInterface $requestBuilder,
        ArrayResultFactory $resultFactory,
        ResultCodeValidator $validator,
        ProfileDetailsHandler $handler
    ) {
        $this->transferFactory = $transferFactory;
        $this->transactionClient = $client;
        $this->requestBuilder = $requestBuilder;
        $this->resultFactory = $resultFactory;
        $this->responseValidator = $validator;
        $this->responseHandler = $handler;
    }
    
    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function execute(array $commandSubject)
    {
        $transferObject = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $response = $this->transactionClient->placeRequest($transferObject);

        $validationResult = $this->responseValidator->validate(['response' => $response]);
        if (! $validationResult->isValid()) {
            throw new \Exception('Unable to create customer profile');
        }

        $this->responseHandler->handle($commandSubject, $response);
    }
}
