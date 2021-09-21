<?php

namespace AuthorizeNet\Core\Test\Unit\Gateway\Request;

use AuthorizeNet\Core\Gateway\Request\OpaqueDataTransactionRequestBuilder;

class OpaqueDataTransactionRequestBuilderTestHelper extends OpaqueDataTransactionRequestBuilder
{
    public function preparePaymentByNonce($field)
    {
        return parent::preparePaymentByNonce($field);
    }

    public function prepareSolutionId(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        return parent::prepareSolutionId($methodInstance);
    }
}
