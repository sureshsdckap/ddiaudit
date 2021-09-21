<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */

namespace AuthorizeNet\VisaCheckout\Gateway\Helper;

use PHPUnit\Framework\TestCase;

class AddressConverterTest extends TestCase
{

    private $_testData = [
        ['vcFieldName' => 'getFirstName', 'addressFieldName' => 'setFirstname' ],
        ['vcFieldName' => 'getLastName', 'addressFieldName' => 'setLastname' ],
        ['vcFieldName' => 'getCompany', 'addressFieldName' => 'setCompany' ],
        ['vcFieldName' => 'getAddress', 'addressFieldName' => 'setStreet' ],
        ['vcFieldName' => 'getCity', 'addressFieldName' => 'setCity' ],
        ['vcFieldName' => 'getState', 'addressFieldName' => 'setRegion' ],
        ['vcFieldName' => 'getCountry', 'addressFieldName' => 'setCountryId' ],
        ['vcFieldName' => 'getZip', 'addressFieldName' => 'setPostcode' ],
        ['vcFieldName' => 'getEmail', 'addressFieldName' => 'setEmail' ],
        ['vcFieldName' => 'getPhoneNumber', 'addressFieldName' => 'setTelephone' ],
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
    protected $visaAddressMock;
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
        
        $this->visaAddressMock = $this->getMockBuilder(\net\authorize\api\contract\v1\CustomerAddressType::class)->disableOriginalConstructor()->getMock();

        $this->converter = new AddressConverter($this->addressFactoryMock);
    }

    /**
     * @param $vcFieldName
     * @param $addressFieldName
     * @dataProvider dataProviderTestVisaToMagentoAddress
     */
    public function testVisaToMagentoAddress($vcFieldName, $addressFieldName)
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
        
        $this->visaAddressMock->expects(static::once())
            ->method($vcFieldName)
            ->willReturn($value);

        static::assertEquals($this->magentoAddressMock, $this->converter->visaToMagentoAddress($this->visaAddressMock));
    }

    public function dataProviderTestVisaToMagentoAddress()
    {
        return $this->_testData;
    }
}
