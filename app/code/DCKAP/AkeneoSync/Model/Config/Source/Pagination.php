<?php
namespace DCKAP\AkeneoSync\Model\Config\Source;

class Pagination implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '10', 'label' => '10'],
            ['value' => '25', 'label' => '25'],
            ['value' => '50', 'label' => '50'],
            ['value' => '100', 'label' => '100']
        ];
    }
}
