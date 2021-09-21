<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;

class ClearDataHandler implements HandlerInterface
{

    const CLEAR_DATA_KEYS = [
        'encKey',
        'encPaymentData',
        'opaque_data',
        'vault_cvv'
    ];
    
    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        SubjectReader $subjectReader
    ) {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Clear response data
     *
     * @param array $handlingSubject
     * @param array $response
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        
        $payment = $paymentDO->getPayment();
        
        foreach (self::CLEAR_DATA_KEYS as $dataKey) {
            if ($payment->hasAdditionalInformation($dataKey)) {
                $payment->unsAdditionalInformation($dataKey);
            }
        }
    }
}
