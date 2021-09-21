<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model;

use AuthorizeNet\Core\Gateway\Config\Config;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface
{
    /**
     * @var Config
     */
    protected $config;
    
    /**
     * @var \Magento\Framework\Logger\Monolog
     */
    protected $logger;

    /**
     * @var Logger\Censor
     */
    protected $censor;

    /**
     * Logger Constructor
     *
     * @param string $name
     * @param Config $config
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        \Magento\Framework\Logger\MonologFactory $loggerFactory,
        $name,
        Config $config,
        Logger\Censor $censor,
        $handlers = [],
        $processors = []
    ) {

        $this->logger = $loggerFactory->create([
            'name' => $name,
            'handlers' => $handlers,
            'processors' => $processors,
        ]);

        $this->config = $config;
        $this->censor = $censor;
    }

    /**
     * @param array|string $data
     * @param array $context
     */
    public function debug($data, array $context = [])
    {
        if (!$this->config->isDebugOn()) {
            return;
        };

        if (is_array($data)) {
            $data = var_export($this->censor->censorSensitiveData($data), true);
        }

        $this->logger->debug($data, $context);
    }

    /**
     * @inheritdoc
     */
    public function emergency($message, array $context = [])
    {
        $this->logger->emergency($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function alert($message, array $context = [])
    {
        $this->logger->alert($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function critical($message, array $context = [])
    {
        $this->logger->critical($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function error($message, array $context = [])
    {
        $this->logger->error($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function warning($message, array $context = [])
    {
        $this->logger->warning($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function notice($message, array $context = [])
    {
        $this->logger->notice($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function info($message, array $context = [])
    {
        $this->logger->info($this->censor->censorSensitiveData($message), $context);
    }

    /**
     * @inheritdoc
     */
    public function log($level, $message, array $context = [])
    {
        
        if ($level == \Psr\Log\LogLevel::DEBUG) {
            $this->debug($message, $context);
            return;
        }

        $this->logger->log($level, $this->censor->censorSensitiveData($message), $context);
    }
}
