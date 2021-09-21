<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Command;

use AuthorizeNet\Core\Gateway\Validator\ResultCodeValidator;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use AuthorizeNet\Core\Gateway\Http\TransferFactory;
use Magento\Payment\Gateway\CommandInterface;
use net\authorize\api\contract\v1\GetMerchantDetailsResponse;

class GetMerchantDetailsCommand implements CommandInterface
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
     * @var ResultCodeValidator
     */
    private $responseValidator;

    /**
     * GetMerchantDetailsCommand constructor.
     *
     * @param TransferFactory $transferFactory
     * @param ClientInterface $client
     * @param BuilderInterface $requestBuilder
     * @param ResultCodeValidator $validator
     */
    public function __construct(
        TransferFactory $transferFactory,
        ClientInterface $client,
        BuilderInterface $requestBuilder,
        ResultCodeValidator $validator
    ) {
        $this->transferFactory = $transferFactory;
        $this->transactionClient = $client;
        $this->requestBuilder = $requestBuilder;
        $this->responseValidator = $validator;
    }

    /**
     * Validate the response and Retrieve the merchant details
     *
     * @param array $commandSubject
     * @return array|\Magento\Payment\Gateway\Command\ResultInterface|null
     * @throws \Exception
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
        if (!$validationResult->isValid()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to get merchant details. Please verify your merchant Login Id and Transaction Key')
            );
        }

        /* @var GetMerchantDetailsResponse $anetResponse */
        $anetResponse = array_shift($response);

        /* @var \net\authorize\api\contract\v1\ProcessorType $processor */
        $processors = $anetResponse->getProcessors();
        $processor = array_shift($processors);

        $details = [
            'isTestMode' => $anetResponse->getIsTestMode(),
            'clientKey' => $anetResponse->getPublicClientKey(),
            'currencies' => $anetResponse->getCurrencies(),
            'paymentMethods' => $anetResponse->getPaymentMethods(),
            'cardTypes' => $processor->getCardTypes()
        ];

        return $details;
    }
}
