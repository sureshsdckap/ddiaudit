<?php

namespace AuthorizeNet\Core\Test\Unit\Block\Payment;

class InfoTestHelper extends \AuthorizeNet\Core\Block\Payment\Info
{

    public function __construct()
    {
        // left empty intentionally
    }

    public function ___getLabel($field)
    {
        return parent::getLabel($field);
    }

    public function ___getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }
}
