<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Model;

class CcTypes
{

    const CC_TYPE_MAP = [
        "AmericanExpress"   => "AE",
        "Discover"          => "DI",
        "JCB"               => "JCB",
        "MasterCard"        => "MC",
        "Visa"              => "VI",
        "Maestro"           => "MI",
        "DinersClub"        => "DN",
        "ChinaUnionPay"     => "CUP"
    ];

    /**
     * Get Available AuthorizeNet Type
     *
     * @return array
     */
    public function getAvailableAuthorizeNetTypes()
    {
        return array_keys(self::CC_TYPE_MAP);
    }

    /**
     * Get CC type map of Anet type
     *
     * @param  string $anetType
     * @return string
     */
    public function getMagentoType($anetType)
    {
        return isset(self::CC_TYPE_MAP[$anetType]) ? self::CC_TYPE_MAP[$anetType] : $anetType;
    }

    /**
     * Get AuthorizeNet Type
     *
     * @param  string $magentoType
     * @return array
     */
    public function getAuthorizeNetType($magentoType)
    {
        return array_search($magentoType, self::CC_TYPE_MAP) ?: $magentoType;
    }
}
