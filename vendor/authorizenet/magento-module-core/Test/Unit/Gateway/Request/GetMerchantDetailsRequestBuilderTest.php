<?php
/**
 *
 */

namespace AuthorizeNet\Core\Gateway\Request;

use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use \AuthorizeNet\Core\Gateway\Request\GetMerchantDetailsRequestBuilder;

class GetMerchantDetailsRequestBuilderTest extends TestCase
{

    /**
     * @var \AuthorizeNet\Core\Gateway\Config\Reader|MockObject
     */
    protected $configReaderMock;

    /**
     * @var \AuthorizeNet\Core\Gateway\Helper\SubjectReader|MockObject
     */
    protected $subjectReaderMock;

    /**
     * @var GetMerchantDetailsRequestBuilder
     */
    protected $builder;

    protected function setUp()
    {


        $this->configReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Config\Reader::class)->disableOriginalConstructor()->getMock();
        $this->subjectReaderMock = $this->getMockBuilder(\AuthorizeNet\Core\Gateway\Helper\SubjectReader::class)->disableOriginalConstructor()->getMock();

        $this->builder = new GetMerchantDetailsRequestBuilder(
            $this->configReaderMock,
            $this->subjectReaderMock,
            'some'
        );
    }

    public function testBuild()
    {

        $subject = [
            'loginId' => 'smLgnId',
            'transactionKey' => 'smTxnKtUrptShtli'
        ];

        $this->subjectReaderMock->expects(static::once())->method('readLoginId')->willReturn($subject['loginId']);
        $this->subjectReaderMock->expects(static::once())->method('readTransactionKey')->willReturn($subject['transactionKey']);

        $request = $this->builder->build($subject);

        /** @var \net\authorize\api\contract\v1\GetMerchantDetailsRequest $anetRequest */
        $anetRequest = $request['request'];

        static::assertEquals($subject['loginId'], $anetRequest->getMerchantAuthentication()->getName());
        static::assertEquals($subject['transactionKey'], $anetRequest->getMerchantAuthentication()->getTransactionKey());
    }
}
