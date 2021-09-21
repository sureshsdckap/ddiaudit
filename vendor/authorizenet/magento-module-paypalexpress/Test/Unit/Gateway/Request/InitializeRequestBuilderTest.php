<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Request;

use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\PayPalExpress\Gateway\Request\InitializeRequestBuilder;
use AuthorizeNet\PayPalExpress\Gateway\Config\Config;
use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use Magento\Framework\UrlInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use PHPUnit\Framework\TestCase;

class InitializeRequestBuilderTest extends TestCase
{
    const TEST_AMOUNT = 5.99;

    /**
     * @var SubjectReader|MockObject
     */
    private $subjectReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|MockObject
     */
    private $configReaderMock;
    
    /**
     * @var InitializeRequestBuilder
     */
    private $requestBuilder;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @var Session|MockObject
     */
    private $sessionMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @var Quote\Address|MockObject
     */
    private $addressMock;

    /**
     * @var string
     */
    private $transactionType;
    /**
     * @var Quote\Item[]|MockObject[]
     */
    private $quoteItemMocks;

    public function setUp()
    {
        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $this->configReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()->getMock();
        $this->sessionMock = $this->getMockBuilder(Session::class)->disableOriginalConstructor()->getMock();
        $this->transactionType = 'authCaptureTransaction';

        $this->requestBuilder = new InitializeRequestBuilder(
            $this->configReaderMock,
            $this->configMock,
            $this->subjectReaderMock,
            $this->transactionType,
            $this->urlBuilderMock,
            $this->sessionMock
        );
    }

    /**
     * @param $addressData
     * @throws \Exception
     * @dataProvider getAddressDataProvider
     */
    public function testBuild($addressData, $items)
    {
        $this->initQuoteMock($addressData, $items);

        $this->quoteMock->expects(static::atLeastOnce())->method('getBaseCurrencyCode')->willReturn('EUR');

        $this->sessionMock->expects(static::any())
            ->method('getQuote')
            ->willReturn($this->quoteMock);

        $paymentAction = 'authorize_capture';

        $this->configMock->expects(static::once())
            ->method('getPaymentAction')
            ->willReturn($paymentAction);

        $solutionId = '12451552';

        $this->configReaderMock->expects(static::once())
            ->method('getSolutionId')
            ->willReturn($solutionId);

        $loginId = 'log1n1d';

        $this->configReaderMock->expects(static::once())
            ->method('getLoginId')
            ->willReturn($loginId);

        $transKey = 'tran5Key';

        $this->configReaderMock->expects(static::once())
            ->method('getTransactionKey')
            ->willReturn($transKey);

        $requestResult = $this->requestBuilder->build([]);

        /** @var \net\authorize\api\contract\v1\CreateTransactionRequest $requestObject */
        $requestObject = $requestResult['request'];

        //assert transaction details
        static::assertEquals($this->transactionType, $requestObject->getTransactionRequest()->getTransactionType());
        static::assertEquals(sprintf('%.2F', self::TEST_AMOUNT), $requestObject->getTransactionRequest()->getAmount());
        static::assertEquals('EUR', $requestObject->getTransactionRequest()->getCurrencyCode());

        //address details
        $requestShipTo = $requestObject->getTransactionRequest()->getShipTo();
        static::assertEquals($addressData['getFirstname'], $requestShipTo->getFirstName());
        static::assertEquals($addressData['getLastname'], $requestShipTo->getLastName());
        static::assertEquals($addressData['getCountry'], $requestShipTo->getCountry());
        static::assertEquals($addressData['getCity'], $requestShipTo->getCity());
        static::assertEquals($addressData['getRegion'], $requestShipTo->getState());
        static::assertEquals($addressData['getStreetFull'], $requestShipTo->getAddress());
        static::assertEquals($addressData['getPostcode'], $requestShipTo->getZip());
        static::assertEquals($addressData['getCompany'], $requestShipTo->getCompany());

        static::assertNull($requestObject->getTransactionRequest()->getLineItems(), 'Do not add line items in initialize request because paypal rejects transactions without any clue why');

        // line items removed, no test
        //$this->assertLineItems($requestObject->getTransactionRequest()->getLineItems(), $items);

        //assert solutionId
        static::assertEquals($solutionId, $requestObject->getTransactionRequest()->getSolution()->getId());

        //assert merchant auth
        static::assertEquals($loginId, $requestObject->getMerchantAuthentication()->getName());
        static::assertEquals($transKey, $requestObject->getMerchantAuthentication()->getTransactionKey());
    }

    /**
     * @param \net\authorize\api\contract\v1\LineItemType[] $lineItems
     * @param $lineItemsData
     */
    private function assertLineItems($lineItems, $lineItemsData)
    {
        foreach ($lineItems as $index => $item) {
            static::assertEquals($lineItemsData[$index]['name'], $item->getName());
            static::assertEquals($lineItemsData[$index]['sku'], $item->getItemId());
            static::assertEquals($lineItemsData[$index]['qty'], $item->getQuantity());
            static::assertEquals($lineItemsData[$index]['base_price_incl_tax'] - $lineItemsData[$index]['base_discount_amount'], $item->getUnitPrice());
            static::assertNull($item->getTaxable()); //make sure taxable is null for paypal
        }
    }

    /**
     * @param $addressData
     * @return void
     */
    private function initQuoteMock($addressData, $items = [])
    {
        //preparing address mock
        $this->addressMock = $this->getMockBuilder(Quote\Address::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($addressData as $method => $value) {
            $this->addressMock->expects(static::once())
                ->method($method)
                ->willReturn($value);
        }

        $paymentMock = $this->getMockBuilder(Quote\Payment::class)->disableOriginalConstructor()->getMock();
        
        $methodMock = $this->getMockBuilder(\Magento\Payment\Model\MethodInterface::class)->getMockForAbstractClass();
        
        $paymentMock->expects(static::any())->method('getMethodInstance')->willReturn($methodMock);
        
        //preparing quote mock
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->setMethods(['getShippingAddress', 'getBaseGrandTotal', 'getPayment', 'getBaseCurrencyCode', 'getItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->quoteMock->expects(static::once())
            ->method('getShippingAddress')
            ->willReturn($this->addressMock);

        $this->quoteMock->expects(static::once())
            ->method('getBaseGrandTotal')
            ->willReturn(self::TEST_AMOUNT);
        
        $this->quoteMock->expects(static::any())
            ->method('getPayment')
            ->willReturn($paymentMock);

        $this->quoteItemMocks = $this->getQuoteItemMocksArray($items);

        $this->quoteMock->expects(static::any())->method('getItems')->willReturn($this->quoteItemMocks);
    }

    private function getQuoteItemMocksArray($items)
    {

        $mockItems = [];

        foreach ($items as $item) {
            //TODO: specify magic methods
            $itemMock = $this->getMockBuilder(Quote\Item::class)->disableOriginalConstructor()
                ->setMethods([
                    'getName',
                    'getSku',
                    'getQty',
                    'getDescription',
                    'getBasePriceInclTax',
                    'getBaseDiscountAmount'
                ])->getMock();

            $itemMock->expects(static::any())->method('getName')->willReturn($item['name']);
            $itemMock->expects(static::any())->method('getSku')->willReturn($item['sku']);
            $itemMock->expects(static::any())->method('getQty')->willReturn($item['qty']);
            $itemMock->expects(static::any())->method('getDescription')->willReturn($item['description']);
            $itemMock->expects(static::any())->method('getBasePriceInclTax')->willReturn($item['base_price_incl_tax']);
            $itemMock->expects(static::any())->method('getBaseDiscountAmount')->willReturn($item['base_discount_amount']);
            $mockItems[] = $itemMock;
        }

        return $mockItems;
    }

    /**
     * @return array
     */
    public function getAddressDataProvider()
    {
        return [
            [
                'addressData' => [
                    'getFirstname' => 'John',
                    'getLastname' => 'Doe',
                    'getCountry' => 'US',
                    'getCity' => 'New York',
                    'getRegion' => 'NY',
                    'getStreetFull' => 'Main st., 17 332',
                    'getPostcode' => '10001',
                    'getCompany' => 'X Inc.'
                ],
                'items' => [
                    [
                        'name' => 'some name',
                        'sku' => 'mySKU',
                        'qty' => 1,
                        'description' => '',
                        'base_price_incl_tax' => 2,
                        'base_discount_amount' => 0.5,
                    ],
                ]
            ]
        ];
    }
}
