<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_VisaCheckout
 */
namespace AuthorizeNet\VisaCheckout\Gateway\Helper;

use net\authorize\api\contract\v1 as AnetAPI;

class AddressConverter
{
    /**
     * @var $addressInterfaceFactory
     */
    protected $addressInterfaceFactory;

    /**
     * AddressConverter Constructor
     *
     * @param \Magento\Quote\Api\Data\AddressInterfaceFactory $addressInterfaceFactory
     */
    public function __construct(
        \Magento\Quote\Api\Data\AddressInterfaceFactory $addressInterfaceFactory
    ) {
    
        $this->addressInterfaceFactory = $addressInterfaceFactory;
    }

    /**
     * Set address data
     *
     * Update customer address types and return updated address info.
     *
     * @param AnetAPI\CustomerAddressType $visaAddress
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function visaToMagentoAddress(AnetAPI\CustomerAddressType $visaAddress)
    {
        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();
        
        $address
            ->setFirstname($visaAddress->getFirstName())
            ->setLastname($visaAddress->getLastName())
            ->setCompany($visaAddress->getCompany())
            ->setStreet($visaAddress->getAddress())
            ->setCity($visaAddress->getCity())
            ->setRegion($visaAddress->getState())
            ->setCountryId($visaAddress->getCountry())
            ->setPostcode($visaAddress->getZip())
            ->setEmail($visaAddress->getEmail())
            ->setTelephone($visaAddress->getPhoneNumber());
        
        return $address;
    }
}
