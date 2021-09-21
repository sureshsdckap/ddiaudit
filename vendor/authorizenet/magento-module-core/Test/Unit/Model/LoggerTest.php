<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model;

use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;

class LoggerTest extends TestCase
{

    /**
     * @var \Magento\Framework\Logger\Monolog|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerMock;
    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Framework\Logger\MonologFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $loggerFactoryMock;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \AuthorizeNet\Core\Model\Logger\Censor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $censorMock;

    protected function setUp()
    {

        $this->loggerMock = $this->getMockBuilder(\Magento\Framework\Logger\Monolog::class)->disableOriginalConstructor()->getMock();
        $this->loggerFactoryMock = $this->getMockBuilder(\Magento\Framework\Logger\MonologFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMockForAbstractClass();

        $handlers = ['asdasd'];
        $name = 'My test logger';
        $processors = [];

        $this->loggerFactoryMock->expects(static::once())
            ->method('create')
            ->with([
                'name' => $name,
                'handlers' => $handlers,
                'processors' => $processors,
            ])
            ->willReturn($this->loggerMock);

        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();

        $this->censorMock = $this->getMockBuilder(\AuthorizeNet\Core\Model\Logger\Censor::class)->getMock();

        $this->logger = new Logger(
            $this->loggerFactoryMock,
            $name,
            $this->configMock,
            $this->censorMock,
            $handlers,
            $processors
        );
    }

    public function testDebugOn()
    {
        $message = ['response' => 'someValue', 'request' => 'somevalue'];
        $context = ['somedata'];

        $this->configMock->expects(static::once())
            ->method('isDebugOn')
            ->willReturn(true);

        $this->loggerMock->expects(static::once())
            ->method('debug')
            ->with(var_export($message, true), $context)
            ->willReturn(null);

        $this->censorMock->expects(static::once())
            ->method('censorSensitiveData')
            ->with($message)
            ->willReturnArgument(0);

        $this->logger->debug($message, $context);
    }

    public function testDebugOff()
    {
        $message = ['response' => 'someValue', 'request' => 'somevalue'];
        $context = ['somedata'];

        $this->configMock->expects(static::once())
            ->method('isDebugOn')
            ->willReturn(false);

        $this->loggerMock->expects(static::never())
            ->method('debug')
            ->willReturn(null);

        $this->censorMock->expects(static::never())->method('censorSensitiveData');

        $this->logger->debug($message, $context);
    }

    /**
     * @param $methodName
     * @dataProvider dataProviderTestSimpleMethods
     */
    public function testSimpleMethods($methodName)
    {

        $message = 'test log message';
        $context = ['somedata'];

        $this->loggerMock->expects(static::once())
            ->method($methodName)
            ->with($message, $context)
            ->willReturn(null);

        $this->censorMock->expects(static::once())
            ->method('censorSensitiveData')
            ->with($message)
            ->willReturnArgument(0);

        $this->logger->{$methodName}($message, $context);
    }

    public function dataProviderTestSimpleMethods()
    {
        return [
            ['methodName' => LogLevel::EMERGENCY],
            ['methodName' => LogLevel::ALERT],
            ['methodName' => LogLevel::CRITICAL],
            ['methodName' => LogLevel::ERROR],
            ['methodName' => LogLevel::WARNING],
            ['methodName' => LogLevel::NOTICE],
            ['methodName' => LogLevel::INFO],
        ];
    }

    public function testLogDebug()
    {

        $message = 'somemessage';
        $context = ['somedata'];

        $this->configMock->expects(static::once())
            ->method('isDebugOn')
            ->willReturn(true);

        $this->loggerMock->expects(static::once())
            ->method('debug')
            ->with($message)
            ->willReturn(null);

        $this->logger->log(LogLevel::DEBUG, $message, $context);
    }

    public function testLog()
    {
        $message = 'somemessage';
        $context = ['somedata'];

        $this->loggerMock->expects(static::never())
            ->method('debug')
            ->with($message)
            ->willReturn(null);

        $this->loggerMock->expects(static::once())
            ->method('log')
            ->with(LogLevel::ALERT, $message, $context)
            ->willReturn(null);

        $this->censorMock->expects(static::once())
            ->method('censorSensitiveData')
            ->with($message)
            ->willReturnArgument(0);

        $this->logger->log(LogLevel::ALERT, $message, $context);
    }
}
