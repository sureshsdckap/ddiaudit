<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Centinel
 */

namespace AuthorizeNet\Centinel\Test\Unit\Model\Cca;

use AuthorizeNet\Centinel\Model\Cca\Validator;
use AuthorizeNet\Centinel\Model\Config;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ValidatorTest extends TestCase
{

    /**
     * @var Config|MockObject
     */
    protected $configMock;

    /**
     * @var \Lcobucci\JWT\Token|MockObject
     */
    protected $tokenMock;

    /**
     * @var \Lcobucci\JWT\Signer\Hmac\Sha256|MockObject
     */
    protected $signerMock;

    /**
     * @var Validator
     */
    protected $validator;

    protected function setUp()
    {
        $this->configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();

        $this->tokenMock = $this->getMockBuilder(\Lcobucci\JWT\Token::class)->disableOriginalConstructor()->getMock();

        $this->signerMock = $this->getMockBuilder(\Lcobucci\JWT\Signer\Hmac\Sha256::class)->disableOriginalConstructor()->getMock();

        $this->validator = new Validator(
            $this->configMock,
            $this->signerMock
        );
    }

    /**
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate($isExpired, $isSigned, $isValidated, $actionCode, $expectedException, $expectedExceptionMessage, $errorDescription = '')
    {

        $apikey = '14124124';

        $payload = new \stdClass();
        $payload->Validated = $isValidated;
        $payload->ActionCode = $actionCode;
        if ($errorDescription) {
            $payload->ErrorDescription = $errorDescription;
        }

        $this->tokenMock->expects(static::any())->method('getClaim')->with('Payload')->willReturn($payload);
        $this->configMock->expects(static::any())->method('getApiKey')->willReturn($apikey);

        $this->tokenMock->expects(static::any())->method('isExpired')->willReturn($isExpired);
        $this->tokenMock->expects(static::any())->method('verify')->with($this->signerMock, $apikey)->willReturn($isSigned);

        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $this->validator->validate($this->tokenMock);
    }

    public function dataProviderTestValidate()
    {
        return [
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => Config::CENTINEL_CCA_ACTION_SUCCESS,
                'expectedException' => null,
                'expectedExceptionMessage' => null,
            ],
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => Config::CENTINEL_CCA_ACTION_NOACTION,
                'expectedException' => null,
                'expectedExceptionMessage' => null,
            ],
            [
                'isExpired' => true,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => Config::CENTINEL_CCA_ACTION_SUCCESS,
                'expectedException' => \Magento\Framework\Exception\LocalizedException::class,
                'expectedExceptionMessage' => 'JWT is expired',
            ],
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => false,
                'actionCode' => Config::CENTINEL_CCA_ACTION_SUCCESS,
                'expectedException' => \Magento\Framework\Exception\LocalizedException::class,
                'expectedExceptionMessage' => 'CCA validation failed',
            ],
            [
                'isExpired' => false,
                'isSigned' => false,
                'isValidated' => true,
                'actionCode' => Config::CENTINEL_CCA_ACTION_SUCCESS,
                'expectedException' => \Magento\Framework\Exception\LocalizedException::class,
                'expectedExceptionMessage' => 'JWT signature verification failed',
            ],
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => Config::CENTINEL_CCA_ACTION_FAILURE,
                'expectedException' => \Exception::class,
                'expectedExceptionMessage' => 'CCA failed: wrong!',
                'errorDescription' => 'wrong!',
            ],
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => \AuthorizeNet\Centinel\Model\Config::CENTINEL_CCA_ACTION_ERROR,
                'expectedException' => \Exception::class,
                'expectedExceptionMessage' => 'CCA failed: wrong!',
                'errorDescription' => 'wrong!',
            ],
            [
                'isExpired' => false,
                'isSigned' => true,
                'isValidated' => true,
                'actionCode' => 'unkAction',
                'expectedException' => \Magento\Framework\Exception\LocalizedException::class,
                'expectedExceptionMessage' => 'CCA failed: unknown action code',
            ],
        ];
    }
}
