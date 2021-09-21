<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Observer;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use \AuthorizeNet\Centinel\Observer\HandleCcaDataObserver;

class HandleCcaDataObserverTest extends TestCase
{

    /**
     * @var \Magento\Checkout\Model\Session|MockObject
     */
    protected $sessionMock;

    /**
     * @var \Magento\Framework\Event\Observer|MockObject
     */
    protected $eventObserverMock;

    /**
     * @var \Magento\Sales\Model\Order\Payment|MockObject
     */
    protected $paymentMock;

    /**
     * @var \Magento\Payment\Model\MethodInterface|MockObject
     */
    protected $methodMock;
    /**
     * @var HandleCcaDataObserver
     */
    protected $testedObserver;

    protected function setUp()
    {
        $this->sessionMock = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->setMethods(['unsData', 'getData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->eventObserverMock = $this->getMockBuilder(\Magento\Framework\Event\Observer::class)
            ->setMethods(['getEvent', 'getOrder', 'getPayment'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)->disableOriginalConstructor()->getMock();
        $this->methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();

        $this->eventObserverMock->expects(static::any())->method('getEvent')->willReturnSelf();
        $this->eventObserverMock->expects(static::any())->method('getOrder')->willReturnSelf();
        $this->eventObserverMock->expects(static::any())->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($this->methodMock);

        $this->testedObserver = new HandleCcaDataObserver(
            $this->sessionMock,
            false
        );
    }

    public function testExecute()
    {
        $this->methodMock->expects(static::any())->method('getConfigData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_ACTIVE_CONFIG_KEY)->willReturn(true);

        $ccaData = new \stdClass();

        $ccaData->Enrolled = true;
        $ccaData->CAVV = '123123213';
        $ccaData->ECIFlag = true;
        $ccaData->PAResStatus = 'success';
        $ccaData->SignatureVerification = true;
        $ccaData->XID = '23232';
        $ccaData->ccaActionCode = true;

        $this->sessionMock->expects(static::any())->method('getData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturn($ccaData);
        $this->sessionMock->expects(static::once())->method('unsData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturnSelf();

        $this->paymentMock->expects(static::exactly(7))->method('setAdditionalInformation')->withConsecutive(
            ['Enrolled', $ccaData->Enrolled],
            ['CAVV', $ccaData->CAVV],
            ['ECIFlag', $ccaData->ECIFlag],
            ['PAResStatus', $ccaData->PAResStatus],
            ['SignatureVerification', $ccaData->SignatureVerification],
            ['XID', $ccaData->XID],
            ['ccaActionCode', $ccaData->ccaActionCode]
        );

        $this->paymentMock->expects(static::once())->method('unsAdditionalInformation')->with('UCAFIndicator')->willReturnSelf();

        $this->testedObserver->execute($this->eventObserverMock);
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage CCA data is empty
     */
    public function testExecuteWithException()
    {
        $this->methodMock->expects(static::any())->method('getConfigData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_ACTIVE_CONFIG_KEY)->willReturn(true);

        $this->sessionMock->expects(static::any())->method('getData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturn(null);
        $this->sessionMock->expects(static::once())->method('unsData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturnSelf();

        $this->testedObserver->execute($this->eventObserverMock);
    }

    public function testExecuteInactive()
    {
        $this->methodMock->expects(static::any())->method('getConfigData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_ACTIVE_CONFIG_KEY)->willReturn(false);

        $this->sessionMock->expects(static::any())->method('getData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturn(null);
        $this->sessionMock->expects(static::once())->method('unsData')->with(\AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_DATA_SESSION_INDEX)->willReturnSelf();

        $this->testedObserver->execute($this->eventObserverMock);
    }
}
