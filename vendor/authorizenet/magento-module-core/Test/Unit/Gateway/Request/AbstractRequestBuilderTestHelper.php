<?php

namespace AuthorizeNet\Core\Test\Unit\Gateway\Request;

class AbstractRequestBuilderTestHelper extends \AuthorizeNet\Core\Gateway\Request\AbstractRequestBuilder
{
    public function build(array $commandSubject)
    {
        // left empty intentionally
    }

    public function getTax($payment)
    {
        return parent::getTax($payment);
    }

    public function ___prepareAddressData(
        \Magento\Payment\Gateway\Data\AddressAdapterInterface $address = null,
        $isShippingAddress = false
    ) {
        return parent::prepareAddressData($address, $isShippingAddress);
    }

    public function getShipping($payment)
    {
        return parent::getShipping($payment);
    }
}
