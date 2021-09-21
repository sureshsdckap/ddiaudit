<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Test\Unit\AuthorizeNet\Core\Plugin;

use AuthorizeNet\Core\Plugin\PaymentTokenRepositoryPlugin;
use PHPUnit\Framework\TestCase;

class PaymentTokenRepositoryPluginTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;
    /**
     * @var \Magento\Vault\Model\PaymentTokenRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $tokenRepositoryMock;
    /**
     * @var \Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $searchResultMock;

    /**
     * @var PaymentTokenRepositoryPlugin
     */
    protected $plugin;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Config::class)->disableOriginalConstructor()->getMock();
        $this->tokenRepositoryMock = $this->getMockBuilder(\Magento\Vault\Model\PaymentTokenRepository::class)->disableOriginalConstructor()->getMock();
        $this->searchResultMock = $this->getMockBuilder(\Magento\Vault\Api\Data\PaymentTokenSearchResultsInterface::class)->getMockForAbstractClass();

        $this->plugin = new PaymentTokenRepositoryPlugin(
            $this->configMock
        );
    }

    public function testAfterGetList()
    {
        $loginId = 'loginId';
        $this->configMock->expects(static::any())->method('getLoginId')->willReturn($loginId);


        $tokensData = [
            [
                'methodCode' => 'someanothermethod',
                'gatewayToken' => '123:12424:loginId',
            ],
            [
                'methodCode' => 'anet_method',
                'gatewayToken' => '123:12424:loginId',
            ],
            [
                'methodCode' => 'anet_method',
                'gatewayToken' => '123:12424:anotherloginId',
            ],
        ];

        $foundTokens = $this->getTokens($tokensData);

        $this->searchResultMock->expects(static::any())->method('getItems')->willReturn($foundTokens);

        $filteredTokens = $foundTokens;
        unset($filteredTokens[2]);

        $this->searchResultMock->expects(static::once())->method('setItems')->with(array_values($filteredTokens))->willReturnSelf();

        static::assertEquals($this->searchResultMock, $this->plugin->afterGetList($this->tokenRepositoryMock, $this->searchResultMock));
    }


    private function getTokens(array $tokesData)
    {
        $result = [];

        foreach ($tokesData as $dataItem) {
            $result[] = $this->getTokenMock(...array_values($dataItem));
        }

        return $result;
    }

    private function getTokenMock($methodCode, $gatewayToken)
    {
        $tokenMock = $this->getMockBuilder(\Magento\Vault\Api\Data\PaymentTokenInterface::class)->getMockForAbstractClass();
        $tokenMock->expects(static::any())->method('getPaymentMethodCode')->willReturn($methodCode);
        $tokenMock->expects(static::any())->method('getGatewayToken')->willReturn($gatewayToken);
        return $tokenMock;
    }
}
