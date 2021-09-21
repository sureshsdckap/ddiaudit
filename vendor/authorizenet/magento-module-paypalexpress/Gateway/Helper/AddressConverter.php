<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_PayPalExpress
 */

namespace AuthorizeNet\PayPalExpress\Gateway\Helper;

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
     * @param \net\authorize\api\contract\v1\NameAndAddressType $paypalAddress
     * @return \Magento\Quote\Api\Data\AddressInterface
     */
    public function paypalAddressToMagento(\net\authorize\api\contract\v1\NameAndAddressType $paypalAddress)
    {

        /** @var \Magento\Quote\Api\Data\AddressInterface $address */
        $address = $this->addressInterfaceFactory->create();

        $address
            ->setFirstname($paypalAddress->getFirstName())
            ->setLastname($paypalAddress->getLastName())
            ->setCompany($paypalAddress->getCompany())
            ->setStreet($paypalAddress->getAddress())
            ->setCity($paypalAddress->getCity())
            ->setRegion($paypalAddress->getState())
            ->setCountryId($paypalAddress->getCountry())
            ->setPostcode($paypalAddress->getZip());

        return $address;
    }
}
