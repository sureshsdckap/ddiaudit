<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Plugin;

class Minification
{

    const EXCLUDE_PATH = 'checkout.visa.com';

    /**
     * @var additionalExcludes
     */
    private $additionalExcludes = [];

    /**
     * Minification Constructor
     *
     * @param additionalExcludes $additionalExcludes
     */
    public function __construct(array $additionalExcludes)
    {
        $this->additionalExcludes = $additionalExcludes;
    }

    /**
     * Around Get Excludes
     *
     * @param  \Magento\Framework\View\Asset\Minification $subject
     * @param  callable $proceed
     * @param  array $contentType
     * @return array $result
     */
    public function aroundGetExcludes(\Magento\Framework\View\Asset\Minification $subject, callable $proceed, $contentType)
    {
        $result = $proceed($contentType);
        if ($contentType != 'js') {
            return $result;
        }
        $result = array_merge($result, $this->additionalExcludes);
        return $result;
    }
}
