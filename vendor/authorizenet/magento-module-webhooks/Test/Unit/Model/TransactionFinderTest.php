<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Webhooks
 */

namespace AuthorizeNet\Webhooks\Test\Unit\Model;

use AuthorizeNet\Webhooks\Model\TransactionFinder;
use PHPUnit\Framework\TestCase;

class TransactionFinderTest extends TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction\Repository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionRepositoryMock;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionCollectionMock;
    /**
     * @var \Magento\Sales\Model\Order\Payment\Transaction|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transactionMock;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaBuilderMock;
    /**
     * @var \Magento\Framework\Api\SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchCriteriaMock;
    /**
     * @var \Magento\Framework\Api\FilterBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterBuilderMock;
    /**
     * @var \Magento\Framework\Api\Filter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $filterMock;
    /**
     * @var TransactionFinder
     */
    protected $transactionFinder;

    public function setUp()
    {
        $this->transactionRepositoryMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction\Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment\Transaction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->transactionCollectionMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteriaBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaMock = $this->getMockBuilder(\Magento\Framework\Api\SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterBuilderMock = $this->getMockBuilder(\Magento\Framework\Api\FilterBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->filterMock = $this->getMockBuilder(\Magento\Framework\Api\Filter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->filterBuilderMock->expects(static::any())
            ->method('setField')
            ->with('txn_id')
            ->willReturnSelf();
        $this->filterBuilderMock->expects(static::any())
            ->method('create')
            ->willReturn($this->filterMock);
        $this->searchCriteriaBuilderMock->expects(static::any())
            ->method('addFilters')
            ->with([$this->filterMock])
            ->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects(static::any())
            ->method('create')
            ->willReturn($this->searchCriteriaMock);
        $this->transactionRepositoryMock->expects(static::any())
            ->method('getList')
            ->willReturn($this->transactionCollectionMock);
        $this->transactionCollectionMock->expects(static::any())
            ->method('getFirstItem')
            ->willReturn($this->transactionMock);


        $this->transactionFinder = new TransactionFinder(
            $this->transactionRepositoryMock,
            $this->searchCriteriaBuilderMock,
            $this->filterBuilderMock
        );
    }

    public function testGetTransaction()
    {
        $transactionId = 10001;
        $this->getTransaction($transactionId);
        static::assertEquals($this->transactionMock, $this->transactionFinder->getTransaction($transactionId));
    }

    protected function getTransaction($transactionId)
    {
        $this->filterBuilderMock->expects(static::any())
            ->method('setValue')
            ->with($transactionId)
            ->willReturnSelf();
        $this->transactionMock->expects(static::any())
            ->method('getTransactionId')
            ->willReturn($transactionId);
    }
}
