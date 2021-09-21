<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Test\Unit\Gateway\Helper;

use PHPUnit\Framework\TestCase;
use \AuthorizeNet\PayPalExpress\Gateway\Helper\AddressConverter;

class AddressConverterTest extends TestCase
{


    private $_testData = [
        ['ppFieldName' => 'getFirstName', 'addressFieldName' => 'setFirstname' ],
        ['ppFieldName' => 'getLastName', 'addressFieldName' => 'setLastname' ],
        ['ppFieldName' => 'getCompany', 'addressFieldName' => 'setCompany' ],
        ['ppFieldName' => 'getAddress', 'addressFieldName' => 'setStreet' ],
        ['ppFieldName' => 'getCity', 'addressFieldName' => 'setCity' ],
        ['ppFieldName' => 'getState', 'addressFieldName' => 'setRegion' ],
        ['ppFieldName' => 'getCountry', 'addressFieldName' => 'setCountryId' ],
        ['ppFieldName' => 'getZip', 'addressFieldName' => 'setPostcode' ],
    ];


    /**
     * @var \Magento\Quote\Api\Data\AddressInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $magentoAddressMock;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;
    /**
     * @var \net\authorize\api\contract\v1\CustomerAddressType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $ppAddressMock;
    /**
     * @var AddressConverter
     */
    protected $converter;

    protected function setUp()
    {

        $this->magentoAddressMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterface::class)->getMockForAbstractClass();
        $this->addressFactoryMock = $this->getMockBuilder(\Magento\Quote\Api\Data\AddressInterfaceFactory::class)->setMethods(['create'])->getMock();

        $this->addressFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->magentoAddressMock);

        $this->ppAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\NameAndAddressType::class)->disableOriginalConstructor()->getMock();

        $this->converter = new AddressConverter($this->addressFactoryMock);
    }

    /**
     * @param $ppFieldName
     * @param $addressFieldName
     * @dataProvider dataProviderTestPaypalAddressToMagento
     */
    public function testPaypalAddressToMagento($ppFieldName, $addressFieldName)
    {

        $value = 'somestringvalue';

        //make all rest methods to return self without any other expectations
        foreach ($this->_testData as $dataItem) {
            if ($dataItem['addressFieldName'] == $addressFieldName) {
                continue;
            }

            $this->magentoAddressMock->expects(static::any())
                ->method($dataItem['addressFieldName'])
                ->willReturnSelf();
        }

        $this->magentoAddressMock->expects(static::once())
            ->method($addressFieldName)
            ->with($value)
            ->willReturnSelf();

        $this->ppAddressMock->expects(static::once())
            ->method($ppFieldName)
            ->willReturn($value);

        static::assertEquals($this->magentoAddressMock, $this->converter->paypalAddressToMagento($this->ppAddressMock));
    }

    public function dataProviderTestPaypalAddressToMagento()
    {
        return $this->_testData;
    }
}
