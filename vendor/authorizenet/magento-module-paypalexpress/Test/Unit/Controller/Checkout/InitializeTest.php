<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Controller\Checkout;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Magento\Payment\Gateway\Command\ResultInterface as CommandResultInterface;
use AuthorizeNet\PayPalExpress\Gateway\Command\InitializeCommand;
use AuthorizeNet\PayPalExpress\Controller\Checkout\Initialize;
use Magento\Payment\Gateway\Command\Result\ArrayResultFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use AuthorizeNet\Core\Model\Logger;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class InitializeTest extends TestCase
{
    const TEST_AMOUNT = 5.99;
    /**
     * @var Initialize
     */
    private $action;

    /**
     * @var InitializeCommand|MockObject
     */
    private $command;

    /**
     * @var Logger|MockObject
     */
    private $logger;

    /**
     * @var ResultFactory|MockObject
     */
    private $resultJsonFactory;

    /**
     * @var ResultInterface|MockObject
     */
    private $jsonResult;

    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var ArrayResultFactory|MockObject
     */
    private $commandResult;

    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var \AuthorizeNet\PayPalExpress\Model\Checkout|MockObject
     */
    protected $checkoutMock;

    public function setUp()
    {
        $this->initResultJsonFactoryMock();

        $this->request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = $this->getMockBuilder(InitializeCommand::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commandResult = $this->getMockBuilder(CommandResultInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->logger = $this->createMock(Logger::class);

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects(static::any())->method('getRequest')->willReturn($this->request);

        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getData', 'setData', 'getQuote'])
            ->getMock();

        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBaseGrandTotal'])
            ->getMock();

        $this->checkoutMock = $this->getMockBuilder(\AuthorizeNet\PayPalExpress\Model\Checkout::class)->disableOriginalConstructor()->getMock();

        $managerHelper = new ObjectManager($this);
        $this->action = $managerHelper->getObject(Initialize::class, [
            'context' => $context,
            'command' => $this->command,
            'session' => $this->session,
            'resultJsonFactory' => $this->resultJsonFactory,
            'checkout' => $this->checkoutMock,
        ]);
    }

    public function testExecute()
    {
        $tokenData = ['status' => true, 'data' => 'EC-BLABLA66789421'];

        $this->checkoutMock->expects(static::any())->method('getTokenData')->willReturn(false);


        $this->commandResult->expects(static::once())
            ->method('get')
            ->willReturn($tokenData);

        $this->command->expects(static::once())
            ->method('execute')
            ->willReturn($this->commandResult);

        $this->jsonResult->expects(static::once())
            ->method('setData')
            ->with(['status' => true, 'data' => $tokenData])
            ->willReturnSelf();

        $this->action->execute();
    }

    /**
     * @dataProvider dataProviderTestExecuteNegative
     */
    public function testExecuteNegative($exceptionClass, $exceptionMessage, $expectedMessage)
    {
        $exception = new $exceptionClass($exceptionMessage);

        $this->checkoutMock->expects(static::any())->method('getTokenData')->willReturn(false);

        $this->command->expects(static::once())
            ->method('execute')
            ->willThrowException($exception);

        $this->jsonResult->expects(static::once())
            ->method('setData')
            ->with([
                'status' => false,
                'error' => __($expectedMessage)
            ])
            ->willReturnSelf();

        $this->action->execute();
    }

    public function dataProviderTestExecuteNegative()
    {
        return [
            [
                'exceptionClass' => \Exception::class,
                'exceptionMessage' => __('We are unable to initialize Paypal Express Checkout.'),
                'expectedMessage' => 'We are unable to initialize Paypal Express Checkout.',
            ],            [
                'exceptionClass' => \Magento\Framework\Exception\LocalizedException::class,
                'exceptionMessage' => __('Something went wrong'),
                'expectedMessage' => 'Something went wrong',
            ],
        ];
    }

    private function initResultJsonFactoryMock()
    {
        $this->jsonResult = $this->getMockBuilder(Json::class)
            ->disableOriginalConstructor()
            ->setMethods(['setData'])
            ->getMock();

        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultJsonFactory->expects(static::once())
            ->method('create')
            ->willReturn($this->jsonResult);
    }
}
