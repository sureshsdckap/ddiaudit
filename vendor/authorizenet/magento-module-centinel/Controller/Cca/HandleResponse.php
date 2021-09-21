<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Controller\Cca;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use AuthorizeNet\Centinel\Model\Cca\Validator;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use AuthorizeNet\Centinel\Model\Config;
use Magento\Checkout\Model\Session;
use AuthorizeNet\Core\Model\Logger;

class HandleResponse extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Session
     */
    private $session;
    /**
     * @var \Lcobucci\JWT\Parser
     */
    private $parser;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * HandleResponse Construct
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Validator $validator
     * @param \Lcobucci\JWT\Parser $parser
     * @param Session $session
     * @param Logger $logger
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Validator $validator,
        \Lcobucci\JWT\Parser $parser,
        Session $session,
        Logger $logger
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->validator = $validator;
        $this->parser = $parser;
        $this->session = $session;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            if (! $jwt = $this->getRequest()->getPost('jwt')) {
                throw new LocalizedException(__('JWT should be provided'));
            }

            $token = $this->parser->parse($jwt);
            $payload = $token->getClaim('Payload');

            $this->validator->validate($token);

            $ccaData = $payload->Payment->ExtendedData;
            $ccaData->ccaActionCode = $payload->ActionCode;

            $this->session->setData(Config::CENTINEL_CCA_DATA_SESSION_INDEX, $ccaData);
            $result->setData(['status' => true]);
        } catch (\Exception $e) {
            $errorMsg = 'Something went wrong. CCA failed.';
            if ($e instanceof LocalizedException) {
                $errorMsg = $e->getMessage();
            }

            $this->logger->error($e->getMessage());
            if (isset($payload)) {
                $this->logger->debug([$payload]);
            }

            $result->setData(['status' => false, 'error' => $errorMsg]);
            $result->setHttpResponseCode(400);
        }

        return $result;
    }
}
