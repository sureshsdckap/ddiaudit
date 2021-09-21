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

class ResultCodeValidatorTest extends TestCase
{

    /**
     * @var AnetAPI\ANetApiResponseType|\PHPUnit_Framework_MockObject_MockObject
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
     * @var ResultCodeValidator
     */
    protected $validator;

    protected function setUp()
    {
        $this->aNetResponseMessagesMock = $this->getMockBuilder(AnetAPI\MessagesType::class)->disableOriginalConstructor()->getMock();

        $this->aNetResponseMock = $this->getMockBuilder(AnetAPI\ANetApiResponseType::class)->disableOriginalConstructor()->getMock();
        $this->aNetResponseMock->expects(static::once())
            ->method('getMessages')
            ->willReturn($this->aNetResponseMessagesMock);

        $this->subjectReaderMock = $this->getMockBuilder(SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->resultInterfaceFactoryMock = $this
            ->getMockBuilder(\Magento\Payment\Gateway\Validator\ResultInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->validator = new ResultCodeValidator(
            $this->resultInterfaceFactoryMock,
            $this->subjectReaderMock
        );
    }


    /**
     * @dataProvider dataProviderTestValidate
     */
    public function testValidate($responseCode, $isValid, $messages)
    {
        
        $this->aNetResponseMessagesMock->expects(static::once())
            ->method('getResultCode')
            ->willReturn($responseCode);
        
        $validationSubject['response'] = $this->aNetResponseMock;
        
        $this->subjectReaderMock->expects(static::once())
            ->method('readResponseObject')
            ->willReturn($validationSubject['response']);

        /** @var \Magento\Payment\Gateway\Validator\ResultInterface|\PHPUnit_Framework_MockObject_MockObject $resultMock */
        $resultMock = $this->createMock(\Magento\Payment\Gateway\Validator\ResultInterface::class);

        $this->resultInterfaceFactoryMock->expects(self::once())
            ->method('create')
            ->with([
                'isValid' => $isValid,
                'failsDescription' => $messages
            ])
            ->willReturn($resultMock);


        $actualMock = $this->validator->validate($validationSubject);

        self::assertEquals($resultMock, $actualMock);
    }

    public function dataProviderTestValidate()
    {
        return [
            ['responseCode' => 'Ok', 'isValid' => true, 'messages' => []],
            ['responseCode' => 'Error', 'isValid' => false, 'messages' => [__('Gateway rejected the transaction.')]],
        ];
    }
}
