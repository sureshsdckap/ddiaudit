<?php
/**
 *
 * @category  AuthorizeNet
 * @package   AuthorizeNet_Core
 */

namespace AuthorizeNet\Core\Gateway\Validator;

use AuthorizeNet\Core\Gateway\Helper\SubjectReader;
use PHPUnit\Framework\TestCase;
use net\authorize\api\contract\v1 as AnetAPI;

class TransactionResponseCodeValidatorTest extends TestCase
{

    /**
     * @var AnetAPI\CreateTransactionResponse|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aNetResponseMock;

    /**
     * @var AnetAPI\MessagesType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aNetResponseMessagesMock;

    /**
     * @var SubjectReader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var \Magento\Payment\Gateway\Validator\ResultInterfaceFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resultInterfaceFactoryMock;

    /**
     * @var TransactionResponseCodeValidator
     */
    protected $validator;

    /**
     * @var \net\authorize\api\contract\v1\TransactionResponseType|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aNetTransactionResponseMock;

    protected function setUp()
    {
        $this->aNetResponseMock = $this->getMockBuilder(AnetAPI\CreateTransactionResponse::class)->disableOriginalConstructor()->getMock();
        $this->aNetTransactionResponseMock = $this->getMockBuilder(AnetAPI\TransactionResponseType::class)->disableOriginalConstructor()->getMock();

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->resultInterfaceFactoryMock = $this
            ->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
    }


    /**
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate($acceptedCodes, $responseCode, $isValid, $messages)
    {

        $this->aNetResponseMock->expects(static::once())
            ->method('getTransactionResponse')
            ->willReturn($this->aNetTransactionResponseMock);

        $validationSubject['response'] = $this->aNetResponseMock;

        $this->subjectReaderMock->expects(static::once())
            ->method('readResponseObject')
            ->willReturn($validationSubject['response']);

        $this->aNetTransactionResponseMock->expects(static::once())
            ->method('getResponseCode')
            ->willReturn($responseCode);

        /** @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->createMock(\Magento\Payment\Gateway\Validator\ResultInterface::class);

        $this->resultInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => $messages
            ])
            ->willReturn($resultMock);

        $this->validator = new TransactionResponseCodeValidator(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock,
            $acceptedCodes
        );

        $actualMock = $this->validator->validate($validationSubject);

        self::assertEquals($resultMock, $actualMock);
    }

    public function dataProviderTestValidate()
    {
        return [
            [
                'acceptedCodes' => [1],
                'responseCode' => 2,
                'isValid' => false,
                'messages' => [__('Gateway rejected the transaction.')],
            ],
            [
                'acceptedCodes' => [1, 4],
                'responseCode' => 2,
                'isValid' => false,
                'messages' => [__('Gateway rejected the transaction.')],
            ],
            [
                'acceptedCodes' => [],
                'responseCode' => 2,
                'isValid' => false,
                'messages' => [__('Gateway rejected the transaction.')],
            ],
            [
                'acceptedCodes' => [2, 3, 4],
                'responseCode' => 2,
                'isValid' => true,
                'messages' => [],
            ],
            [
                'acceptedCodes' => [2],
                'responseCode' => 2,
                'isValid' => true,
                'messages' => [],
            ],
        
        ];
    }
    
    public function testValidateWithIncorrectResponseType()
    {


        $this->aNetResponseMock->expects(static::once())
            ->method('getTransactionResponse')
            ->willReturn(true);

        $validationSubject['response'] = $this->aNetResponseMock;

        $this->subjectReaderMock->expects(static::once())
            ->method('readResponseObject')
            ->willReturn($validationSubject['response']);

        /** @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->createMock(\Magento\Payment\Gateway\Validator\ResultInterface::class);

        $this->resultInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => false,
                'failsDescription' => ['Gateway rejected the transaction.']
            ])
            ->willReturn($resultMock);

        $this->validator = new TransactionResponseCodeValidator(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock,
            [1]
        );

        $actualMock = $this->validator->validate($validationSubject);

        self::assertEquals($resultMock, $actualMock);
    }
}
