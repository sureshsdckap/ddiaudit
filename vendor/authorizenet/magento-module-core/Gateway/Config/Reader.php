<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Config;

class Reader
{

    /**
     * Get Authorize.Net login id
     *
     * @param  \Magento\Payment\Model\MethodInterface
     * @return string
     */
    public function getLoginId(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        return $methodInstance->getConfigData(Config::KEY_LOGIN_ID);
    }
    
    /**
     * Get Authorize.Net transaction key
     *
     * @param  \Magento\Payment\Model\MethodInterface
     * @return string
     */
    public function getTransactionKey(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        return $methodInstance->getConfigData(Config::KEY_TRANS_KEY);
    }

    /**
     * Get Authorize.Net solution id.
     *
     * @param  \Magento\Payment\Model\MethodInterface
     * @return string
     */
    public function getSolutionId(\Magento\Payment\Model\MethodInterface $methodInstance)
    {
        return $methodInstance->getConfigData(Config::KEY_SOLUTION_ID);
    }
}
