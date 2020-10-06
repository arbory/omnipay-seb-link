<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Tests\TestCase;

class CompleteRequestTest extends TestCase
{
    /**
     * @var \Omnipay\SebLink\Message\CompleteRequest
     */
    private $complete_request;

    private $http_request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $this->http_request = $this->getHttpRequest();
        $this->complete_request = new CompleteRequest($client, $this->http_request);
    }

    public function testValidateWithUnexistingIBSERVICE()
    {
        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Unknown IB_SERVICE code");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithInvalidIBSERVICE()
    {
        $this->http_request->query->set('IB_SERVICE', 'xxx');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Unknown IB_SERVICE code");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithUnexistingIBSNDID()
    {
        $this->http_request->query->set('IB_SERVICE', '0003');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid Bank ID");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithInvalidIBSNDID()
    {
        $this->http_request->query->set('IB_SERVICE', '0003');
        $this->http_request->query->set('IB_SND_ID', 'xxx');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid Bank ID");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithUnexistingIBRECID()
    {
        $this->http_request->query->set('IB_SERVICE', '0003');
        $this->http_request->query->set('IB_SND_ID', 'SEBUB');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid Merchant ID");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithInvalidIBRECID()
    {
        $this->http_request->query->set('IB_SERVICE', '0003');
        $this->http_request->query->set('IB_SND_ID', 'SEBUB');
        $this->http_request->query->set('IB_REC_ID', '123');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid Merchant ID");

        $this->complete_request->validateResponseParameters();
    }

    public function testValidateWithValidIBRECID()
    {
        $this->complete_request->setMerchantId("123");
        $this->complete_request->setPublicCertificatePath("tests/Fixtures/key.pub");

        $this->http_request->query->set('IB_SERVICE', '0003');
        $this->http_request->query->set('IB_SND_ID', 'SEBUB');
        $this->http_request->query->set('IB_REC_ID', '123');

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Data is corrupt or has been changed by a third party");
        $this->complete_request->validateResponseParameters();
    }
}
