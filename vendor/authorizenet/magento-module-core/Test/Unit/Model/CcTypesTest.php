<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\AuthorizeNet\Core;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use AuthorizeNet\Core\Model\CcTypes;

class CcTypesTest extends TestCase
{

    /**
     * @var CcTypes
     */
    protected $ccTypesModel;

    protected function setUp()
    {

        $this->ccTypesModel = new CcTypes();
    }

    /**
     * @param $type
     * @param $expected
     * @dataProvider dataProviderTestGetMagentoType
     */
    public function testGetMagentoType($type, $expected)
    {
        static::assertEquals($expected, $this->ccTypesModel->getMagentoType($type));
    }

    /**
     * @param $expected
     * @param $type
     * @dataProvider dataProviderTestGetMagentoType
     */
    public function testGetAnetType($expected, $type)
    {
        static::assertEquals($expected, $this->ccTypesModel->getAuthorizeNetType($type));
    }

    public function dataProviderTestGetMagentoType()
    {
        return [
            ['AmericanExpress', 'AE'],
            ["Discover", "DI"],
            ["JCB", "JCB"],
            ["MasterCard", "MC"],
            ["Visa", "VI"],
            ["Maestro", "MI"],
            ["DinersClub", "DN"],
            ["ChinaUnionPay", "CUP"],
        ];
    }

    public function testGetAvailableAuthorizeNetTypes()
    {
        static::assertEquals(array_keys(CcTypes::CC_TYPE_MAP), $this->ccTypesModel->getAvailableAuthorizeNetTypes());
    }
}
