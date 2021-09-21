<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

class PaymentAdditionalDataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * Main action method.
     *
     * This method call of the observer for the payment method to assign data to anet credit card.
     * It's set payment additional information data.
     *
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $method = $this->readMethodArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->getAdditionalInformationList($method) as $additionalInformationKey) {
            if (isset($additionalData[$additionalInformationKey])) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            } else {
                $paymentInfo->unsAdditionalInformation($additionalInformationKey);
            }
        }
    }

    /**
     * Get additional information list
     *
     * Get config data of payment additional information keys.
     *
     * @param  MethodInterface $method
     * @return array
     */
    protected function getAdditionalInformationList(MethodInterface $method)
    {
        return explode(',', (string)$method->getConfigData('paymentAdditionalInformationKeys'));
    }
}
