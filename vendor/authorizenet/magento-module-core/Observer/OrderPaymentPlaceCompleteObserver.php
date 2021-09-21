<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Observer;

use AuthorizeNet\Core\Gateway\Command\CreateProfileCommand;
use Magento\Framework\Message\ManagerInterface;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Framework\Event\Observer;

class OrderPaymentPlaceCompleteObserver implements ObserverInterface
{
    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var CreateProfileCommand
     */
    private $conmand;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var string
     */
    private $paymentMethodCode;

    /**
     * OrderPaymentPlaceCompleteObserver Constructor
     *
     * @param CreateProfileCommand $command
     * @param SubjectReader $subjectReader
     * @param ManagerInterface $messageManager
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     * @param $paymentMethodCode
     */
    public function __construct(
        CreateProfileCommand $command,
        SubjectReader $subjectReader,
        ManagerInterface $messageManager,
        PaymentDataObjectFactory $paymentDataObjectFactory,
        $paymentMethodCode
    ) {
        $this->conmand = $command;
        $this->subjectReader = $subjectReader;
        $this->messageManager = $messageManager;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->paymentMethodCode = $paymentMethodCode;
    }

    /**
     * Main action method.
     *
     * Save payment details.
     * Applied some conditions to check payment and profile.
     * If payment can't save then throw error notification message.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var Payment $payment */
        $payment = $observer->getEvent()->getPayment();

        if ($payment->getMethod() != $this->paymentMethodCode) {
            return;
        }

        $subject = ['payment' => $this->paymentDataObjectFactory->create($payment)];

        if (! $this->canCreateProfile($subject)) {
            return;
        }

        try {
            $this->conmand->execute($subject);
        } catch (\Exception $e) {
            $this->messageManager->addNoticeMessage(
                __('Something went wrong while saving your payment details for later use.')
            );
        }
    }

    /**
     * To Check the condition to create profile
     *
     * @param array $subject
     * @return bool
     */
    private function canCreateProfile(array $subject)
    {
        /** @var Payment $payment */
        $payment = $this->subjectReader->readPayment($subject)->getPayment();
        return !$payment->getParentTransactionId() && $this->subjectReader->readIsTokenEnabled($subject);
    }
}
