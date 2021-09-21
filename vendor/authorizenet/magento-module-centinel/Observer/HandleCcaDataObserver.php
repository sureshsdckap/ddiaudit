<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use AuthorizeNet\Centinel\Model\Config;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class HandleCcaDataObserver implements ObserverInterface
{
    /**
     * @var ccaDataFields
     */
    private $ccaDataFields = [
        'Enrolled',
        'CAVV',
        'ECIFlag',
        'PAResStatus',
        'SignatureVerification',
        'XID',
        'UCAFIndicator',
        'ccaActionCode'
    ];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var bool
     */
    private $isAdminArea;

    /**
     * HandleCcaDataObserver contractor
     *
     * @param Session $session
     * @param bool $isAdminArea
     */
    public function __construct(
        Session $session,
        $isAdminArea = false
    ) {
        $this->session = $session;
        $this->isAdminArea = $isAdminArea;
    }

    /**
     * Check the CCA data before place an order.
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $observer->getEvent()->getOrder()->getPayment();
            $ccaData = $this->session->getData(Config::CENTINEL_CCA_DATA_SESSION_INDEX);
            $isCentinelActive = $payment->getMethodInstance()->getConfigData(Config::CENTINEL_ACTIVE_CONFIG_KEY);

            if (!$isCentinelActive || $this->isAdminArea) {
                return;
            }

            if (!$ccaData) {
                throw new LocalizedException(__('CCA data is empty'));
            }

            foreach ($this->ccaDataFields as $field) {
                if (property_exists($ccaData, $field)) {
                    $payment->setAdditionalInformation($field, $ccaData->{$field});
                } else {
                    $payment->unsAdditionalInformation($field);
                }
            }
        } finally {
            $this->session->unsData(Config::CENTINEL_CCA_DATA_SESSION_INDEX);
        }
    }
}
