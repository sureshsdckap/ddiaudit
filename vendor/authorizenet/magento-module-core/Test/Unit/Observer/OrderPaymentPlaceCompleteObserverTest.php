<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\Observer;

use AuthorizeNet\Core\Observer\OrderPaymentPlaceCompleteObserver;
use PHPUnit\Framework\TestCase;

class OrderPaymentPlaceCompleteObserverTest extends TestCase
{
    /**
     * @var \AuthorizeNet\Core\Gateway\Command\CreateProfileCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commandMock;
    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;
    /**
     * @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $messageManagerMock;
    /**
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentDataObjectFactoryMock;

    protected $paymentCode = 'anet_core_test';

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentMock;

    protected $paymentDoMock;
    /**
     * @var OrderPaymentPlaceCompleteObserver
     */
    protected $observer;

    protected function setUp()
    {

        $this->commandMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Command\CreateProfileCommand::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->messageManagerMock = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)->disableOriginalConstructor()->getMock();
        $this->paymentDataObjectFactoryMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectFactory::class)->disableOriginalConstructor()->getMock();

        $this->eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)->setMethods(['getEvent', 'getPayment'])->disableOriginalConstructor()->getMock();
        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->paymentDoMock = $this->getMockBuilder(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class)->getMockForAbstractClass();

        $this->eventObserverMock->expects(static::any())->method('getEvent')->willReturnSelf();
        $this->eventObserverMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);
        $this->subjectReaderMock->expects(static::any())->method('readPayment')->willReturn($this->paymentDoMock);
        $this->paymentDoMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);

        $this->observer = new OrderPaymentPlaceCompleteObserver(
            $this->commandMock,
            $this->subjectReaderMock,
            $this->messageManagerMock,
            $this->paymentDataObjectFactoryMock,
            $this->paymentCode
        );
    }

    public function testExecute()
    {

        $this->paymentMock->expects(static::any())->method('getMethod')->willReturn($this->paymentCode);
        $this->paymentDataObjectFactoryMock->expects(static::once())->method('create')->with($this->paymentMock)->willReturn($this->paymentDoMock);

        $this->paymentMock->expects(static::any())->method('getParentTransactionId')->willReturn(null);
        $this->subjectReaderMock->expects(static::any())->method('readIsTokenEnabled')->with(['payment' => $this->paymentDoMock])->willReturn(true);

        $this->commandMock->expects(static::once())->method('execute')->with(['payment' => $this->paymentDoMock]);

        $this->observer->execute($this->eventObserverMock);
    }


    public function testExecuteAnotherCode()
    {

        $this->paymentMock->expects(static::any())->method('getMethod')->willReturn('another_code');
        $this->paymentDataObjectFactoryMock->expects(static::any())->method('create')->with($this->paymentMock)->willReturn($this->paymentDoMock);

        $this->paymentMock->expects(static::any())->method('getParentTransactionId')->willReturn(null);
        $this->subjectReaderMock->expects(static::any())->method('readIsTokenEnabled')->with(['payment' => $this->paymentDoMock])->willReturn(true);

        $this->commandMock->expects(static::never())->method('execute')->with(['payment' => $this->paymentDoMock]);

        $this->observer->execute($this->eventObserverMock);
    }

    public function testExecuteNoProfileCreation()
    {

        $this->paymentMock->expects(static::any())->method('getMethod')->willReturn($this->paymentCode);
        $this->paymentDataObjectFactoryMock->expects(static::any())->method('create')->with($this->paymentMock)->willReturn($this->paymentDoMock);

        $this->paymentMock->expects(static::any())->method('getParentTransactionId')->willReturn(null);
        $this->subjectReaderMock->expects(static::any())->method('readIsTokenEnabled')->with(['payment' => $this->paymentDoMock])->willReturn(false);

        $this->commandMock->expects(static::never())->method('execute')->with(['payment' => $this->paymentDoMock]);

        $this->observer->execute($this->eventObserverMock);
    }

    public function testExecuteWithException()
    {

        $exceptionMessage = 'some exceptions message';
        $exception = new \Exception($exceptionMessage);

        $this->paymentMock->expects(static::any())->method('getMethod')->willReturn($this->paymentCode);
        $this->paymentDataObjectFactoryMock->expects(static::once())->method('create')->with($this->paymentMock)->willReturn($this->paymentDoMock);

        $this->paymentMock->expects(static::any())->method('getParentTransactionId')->willReturn(null);
        $this->subjectReaderMock->expects(static::any())->method('readIsTokenEnabled')->with(['payment' => $this->paymentDoMock])->willReturn(true);

        $this->commandMock->expects(static::once())->method('execute')->with(['payment' => $this->paymentDoMock])->willThrowException($exception);

        $this->messageManagerMock->expects(static::once())->method('addNoticeMessage')->with(__('Something went wrong while saving your payment details for later use.'));

        $this->observer->execute($this->eventObserverMock);
    }
}
