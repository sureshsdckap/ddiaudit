<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Command;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Gateway\Validator\TransactionResponseCodeValidator;
use AuthorizeNet\PayPalExpress\Gateway\Request\InitializeRequestBuilder;
use AuthorizeNet\PayPalExpress\Gateway\Command\InitializeCommand;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use AuthorizeNet\Core\Gateway\Http\Client\TransactionClient;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use AuthorizeNet\Core\Gateway\Http\TransferFactory;
use PHPUnit\Framework\TestCase;

use net\authorize\api\contract\v1 as AnetAPI;

class InitializeCommandTest extends TestCase
{
    const TEST_TOKEN = 'EC-68H583408X934550T';
    const TEST_TRANS_ID = 'tran51D';


    /**
     * @var InitializeCommand|MockObject
     */
    private $command;

    /**
     * @var TransferFactory|MockObject
     */
    private $transferFactory;

    /**
     * @var TransferInterface|MockObject
     */
    private $transferInterface;

    /**
     * @var InitializeRequestBuilder|MockObject
     */
    private $requestBuilder;

    /**
     * @var TransactionClient|MockObject
     */
    private $transactionClient;

    /**
     * @var TransactionResponseCodeValidator|MockObject
     */
    private $responseValidator;

    /**
     * @var ResultInterface|MockObject
     */
    private $validationResult;

    /**
     * @var ArrayResultFactory|MockObject
     */
    private $arrayResultFactory;

    /**
     * @var \Magento\Payment\Gateway\Command\Result\ArrayResult|MockObject
     */
    private $arrayResultMock;

    public function setUp()
    {
        $this->transferFactory = $this->getMockBuilder(TransferFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create', 'getBody', 'setBody'])
            ->getMock();

        $this->transferInterface = $this->getMockBuilder(TransferInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestBuilder = $this->getMockBuilder(InitializeRequestBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->transactionClient = $this->getMockBuilder(TransactionClient::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseValidator = $this->getMockBuilder(TransactionResponseCodeValidator::class)
            ->disableOriginalConstructor()
            ->setMethods(['validate', 'isValid', 'getFailsDescription'])
            ->getMock();

        $this->arrayResultFactory = $this->getMockBuilder(ArrayResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->arrayResultMock = $this->getMockBuilder(\Magento\Payment\Gateway\Command\Result\ArrayResult::class);

        $this->validationResult = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['isValid', 'getFailsDescription'])
            ->getMock();

        $this->command = new InitializeCommand(
            $this->transferFactory,
            $this->requestBuilder,
            $this->transactionClient,
            $this->responseValidator,
            $this->arrayResultFactory
        );
    }

    public function testExecute()
    {
        $anetResponse = new AnetAPI\CreateTransactionResponse();
        $transactionResponseType =  new AnetAPI\TransactionResponseType();
        $secureAcceptanceType = new AnetAPI\TransactionResponseType\SecureAcceptanceAType();

        $transactionResponseType->setTransId(self::TEST_TRANS_ID);

        $secureAcceptanceType->setSecureAcceptanceUrl(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . self::TEST_TOKEN
        );

        $transactionResponseType->setSecureAcceptance($secureAcceptanceType);
        $anetResponse->setTransactionResponse($transactionResponseType);

        $requestMock = ['somerequest'];
        
        $this->transferFactory->expects(static::once())
            ->method('create')
            ->with($requestMock)
            ->willReturn($this->transferInterface);

        $this->requestBuilder->expects(static::once())
            ->method('build')
            ->willReturn($requestMock);

        $this->transactionClient->expects(static::once())
            ->method('placeRequest')
            ->willReturn([$anetResponse]);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(true);

        $this->responseValidator->expects(static::once())
            ->method('validate')
            ->willReturn($this->validationResult);

        $this->arrayResultFactory->expects(static::once())
            ->method('create')
            ->with([
                    'array' => [
                        'transId' => self::TEST_TRANS_ID,
                        'token' => self::TEST_TOKEN
                    ]
                ])->willReturn($this->arrayResultMock);

        static::assertEquals($this->arrayResultMock, $this->command->execute([]));
    }


    /**
     * @expectedException \Magento\Payment\Gateway\Command\CommandException
     */
    public function testExecuteWithException()
    {
        $anetResponse = new AnetAPI\CreateTransactionResponse();
        $transactionResponseType =  new AnetAPI\TransactionResponseType();
        $secureAcceptanceType = new AnetAPI\TransactionResponseType\SecureAcceptanceAType();

        $transactionResponseType->setTransId(self::TEST_TRANS_ID);

        $secureAcceptanceType->setSecureAcceptanceUrl(
            'https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . self::TEST_TOKEN
        );

        $transactionResponseType->setSecureAcceptance($secureAcceptanceType);
        $anetResponse->setTransactionResponse($transactionResponseType);

        $requestMock = ['somerequest'];

        $this->transferFactory->expects(static::once())
            ->method('create')
            ->with($requestMock)
            ->willReturn($this->transferInterface);

        $this->requestBuilder->expects(static::once())
            ->method('build')
            ->willReturn($requestMock);

        $this->transactionClient->expects(static::once())
            ->method('placeRequest')
            ->willReturn([$anetResponse]);

        $this->validationResult->expects(static::once())
            ->method('isValid')
            ->willReturn(false);

        $this->responseValidator->expects(static::once())
            ->method('validate')
            ->willReturn($this->validationResult);

        $this->command->execute([]);
    }
}
