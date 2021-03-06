<?php

namespace Omnipay\SebLink;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\SebLink\Gateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'merchantId' => 'MERCHANT1',
            'merchantBankAccount' => 'SEB001',
            'merchantName' => 'some merchant',
            'returnUrl' => 'http://localhost:8080/omnipay/banklink/',
            'privateCertificatePath' => 'tests/Fixtures/key.pem',
            'publicCertificatePath' => 'tests/Fixtures/key.pub',
            'transactionReference' => 'abc123',
            'description' => 'purchase description',
            'amount' => '10.00',
            'currency' => 'EUR',
        );
    }

    public function testPurchaseSuccess()
    {
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\SebLink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://ibanka.seb.lv/ipc/epakindex.jsp', $response->getRedirectUrl());

        $this->assertEquals(array(
            'IB_SND_ID' => 'MERCHANT1',
            'IB_SERVICE' => '0002',
            'IB_VERSION' => '001',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_NAME' => 'some merchant',
            'IB_PAYMENT_ID' => 'abc123',
            'IB_PAYMENT_DESC' => 'purchase description',
            'IB_CRC' => 'sB43Oq1+RXLKZUeJRp4kpMgDH6T2nmabjoVw1qvik4E4uP+cmn7fv8uGLc5yG/MWpglXgY05OrYJJJxYJNojmifaJ61w3JFf1H9ggqaqpkdoUPEbioHrhKxyOPtSogyGAEVY3zOj4OtcOx7bKipqi4SfWNVoFK7RaCt4Js7NlEZSlstntzDmlet2gR0bxIr4Sd5J+394R6Uf7brLo9NG1/1eo9a6z5Dp3/cIlPHK4sWHVH1oa5Et2rgIREcEdpAPF4c/90a+Cyhy/hT3VCZdf8lMBDSUjAWT9VUJjYl72pTVrNsLVe6XY1Bm6HfLmyitwlX3IJyY3x4Zr09re+caCA==',
            'IB_FEEDBACK' => 'http://localhost:8080/omnipay/banklink/',
            'IB_LANG' => 'LAT'
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseSuccessWithPassphrasedPrivateKey()
    {
        $options = $this->options;
        $options['privateCertificatePath'] = 'tests/Fixtures/key_with_passphrase.pem';
        $options['privateCertificatePassphrase'] = 'foobar';

        $response = $this->gateway->purchase($options)->send();

        $this->assertInstanceOf('\Omnipay\SebLink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://ibanka.seb.lv/ipc/epakindex.jsp', $response->getRedirectUrl());

        $this->assertEquals(array(
            'IB_SND_ID' => 'MERCHANT1',
            'IB_SERVICE' => '0002',
            'IB_VERSION' => '001',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_NAME' => 'some merchant',
            'IB_PAYMENT_ID' => 'abc123',
            'IB_PAYMENT_DESC' => 'purchase description',
            'IB_CRC' => 'jMLqwHPsWRio0OcZz0ALuk8AivkncW+nlhuA2ePFWj3Ulp1Q57KryM8T8rtKDxbJoqraWgMeDi90thp/aXCefS5UX/4AqJAe8qUURcS3kIae6V+kkQAm6LLZIL+jyZGn9zeNCorPA/Eh6IQJ2vFNyaFNjnrP2TWCdBJf/LHL8HgbDFwDL5HZ/JMgQL7nDA25AN07jTznGNPGkk5xzuJkov+1Zim8TiKZYo6OLDOUSsPOM6c5EnhkAF4fqG0jXFmbPW/YCYUHUbPD5xbG0kQdiOz+x4nx12mZ18U025aw1WSBhbTwX6yQIvX82lPWMZJVvEOg3cgZs1eUk8+AeiSQ5g==',
            'IB_FEEDBACK' => 'http://localhost:8080/omnipay/banklink/',
            'IB_LANG' => 'LAT'
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseCompleteSuccess()
    {
        $postData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_SERVICE' => '0003',
            'IB_VERSION' => '001',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_REC_ACC' => 'XXXXXXXXXX',
            'IB_REC_NAME' => 'Shop',
            'IB_PAYER_ACC' => 'SEB0001LV',
            'IB_PAYER_NAME' => 'John Mayer',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_PAYMENT_DATE' => '10.03.2019',
            'IB_PAYMENT_TIME' => '21:12:34',
            'IB_CRC' => 'oHMsqpUTBxPVv/mRV+nOqxmcPHe3jt0w/QLO+xORyWP25bgkJpxP/afn2eUUIc6rlGrAZsTl4E0e3/12LIDurwMnYbGIyqGzBDhuzf9rB6IewiTgXuota4Yb8bcRR3EI5Dzy2WeGZxgNqH+9H+zsAWnfQlYD4NNWxj8oJwsxSIljZSsxGCpm2zvntfVs4RWGov4BrbKMh0UAbSDreweSRgk5OM7aKv/RCgkZOBhqqIeLOelRq8mYwtwp0JE3X+ahMsPuIXWWzoGrAgDYKuNonPiiRlFUnK3QTaQ/DuHExZOafxCYT3uTPBa8/uFFwbLF2Lh5DUI4DRhCEnHFIZU9Pg==',
            'IB_LANG' => 'LAT',
            'IB_FROM_SERVER' => 'Y',
        );

        $this->getHttpRequest()->setMethod('POST');
        $this->getHttpRequest()->request->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isCancelled());
        $this->assertSame('UB0000000000015', $response->getTransactionReference());
        $this->assertSame('Payment was successful', $response->getMessage());
    }

    public function testPurchaseCompleteSuccessWithGET()
    {
        $getData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_SERVICE' => '0003',
            'IB_VERSION' => '001',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_REC_ACC' => 'XXXXXXXXXX',
            'IB_REC_NAME' => 'Shop',
            'IB_PAYER_ACC' => 'SEB0001LV',
            'IB_PAYER_NAME' => 'John Mayer',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_PAYMENT_DATE' => '10.03.2019',
            'IB_PAYMENT_TIME' => '21:12:34',
            'IB_CRC' => 'oHMsqpUTBxPVv/mRV+nOqxmcPHe3jt0w/QLO+xORyWP25bgkJpxP/afn2eUUIc6rlGrAZsTl4E0e3/12LIDurwMnYbGIyqGzBDhuzf9rB6IewiTgXuota4Yb8bcRR3EI5Dzy2WeGZxgNqH+9H+zsAWnfQlYD4NNWxj8oJwsxSIljZSsxGCpm2zvntfVs4RWGov4BrbKMh0UAbSDreweSRgk5OM7aKv/RCgkZOBhqqIeLOelRq8mYwtwp0JE3X+ahMsPuIXWWzoGrAgDYKuNonPiiRlFUnK3QTaQ/DuHExZOafxCYT3uTPBa8/uFFwbLF2Lh5DUI4DRhCEnHFIZU9Pg==',
            'IB_LANG' => 'LAT',
            'IB_FROM_SERVER' => 'Y',
        );

        $this->getHttpRequest()->query->replace($getData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isCancelled());
        $this->assertSame('UB0000000000015', $response->getTransactionReference());
        $this->assertSame('Payment was successful', $response->getMessage());
    }

    public function testPurchaseCompleteWithOrderStatusRequest()
    {
        $postData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_SERVICE' => '0004',
            'IB_VERSION' => '001',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_FROM_SERVER' => 'Y',
            'IB_STATUS' => 'ACCOMPLISHED',
            'IB_CRC' => 'Y4KDEHvrLffVAjSD919dM0ghYgVUje8B3VBBvwcUTi+emc3VcGDG3KcH+OkSsbEcBZ2Zl8jsCBdHinu8tMicx7qRS9imP6DWruh/BLAfpEaeWks1zJDslVjfiaJ3nFI232YN9+pqe1a2c63oJZ3H2vIJlXpHSor7CpNvJBzIn6By02OjuWFAaaXLnNG0CsCN1SnNJRF4vi/aBZ6sWaHp5UevCxRpvELLTTNnJiFy3AleO9ZezkRzAA9BvuphDzYH62QaRRmE6OUvLY8c+1LeGi/fHksberoXYBPkOenGSKPG9Ee40hr8KqOdB7i+O7vL8//P3tS8DoryEF6AW5h1ew==',
            'IB_LANG' => 'LAT',
        );

        $this->getHttpRequest()->query->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isCancelled());
        $this->assertSame('UB0000000000015', $response->getTransactionReference());
        $this->assertSame('Payment was successful', $response->getMessage());
    }

    public function testPurchaseCompleteFailed()
    {
        $postData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_SERVICE' => '0004',
            'IB_VERSION' => '001',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_FROM_SERVER' => 'N',
            'IB_STATUS' => 'CANCELLED',
            'IB_CRC' => 'AH9d+xttxCVKlg5+JOUXeR1ZbZkVmhwwoKoCZm+Lv3AvmB/4U12124bd9b5sf7iDn7e/L7COhFNMTEvzi0V6z9j71IuiKsSXHZOpwL1B8caL/Ozpbse8xXBF+958j7vVTS2vgqfWl4peh11j8lRoK2ug3PCO1Mn10SerOJG6xN0tFuqsQ7PIOoOeZw2NL/zj0zdWlK6RGKtyZbypTcX1dYdCKsm3b4+3vYbd5+7AtWyd0HHYw5kG5b/Dqt3+pzoutwkFTrhN03R8IzIR7xxP1hBaK7xihCrHJ4Lv1x2HFCY9etlZQDmkLwK77nZ+MstnBh32ITED5CyRQb6RTTA8OQ==',
            'IB_LANG' => 'LAT',
        );

        $this->getHttpRequest()->query->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertTrue($response->isCancelled());
        $this->assertSame('UB0000000000015', $response->getTransactionReference());
        $this->assertSame('Payment canceled by user', $response->getMessage());
    }

    public function testPurchaseCompleteFailedWithForgedSignature()
    {
        $postData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_SERVICE' => '0004',
            'IB_VERSION' => '001',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_FROM_SERVER' => 'Y',
            'IB_STATUS' => 'CANCELLED',
            'IB_CRC' => 'RJOsoKvgIuXy4mcB26sadsadd=',
            'IB_LANG' => 'LAT',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Data is corrupt or has been changed by a third party");

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    public function testPurchaseCompleteFailedWithInvalidRequest()
    {
        $postData = array(
            'some_param' => 'x',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Unknown IB_SERVICE code");

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    // test with missing IB_REF parameter
    public function testPurchaseCompleteFailedWithIncompleteRequest()
    {
        $postData = array(
            'IB_SND_ID' => 'SEBUB',
            'IB_VERSION' => '001',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_REC_ACC' => 'XXXXXXXXXX',
            'IB_REC_NAME' => 'Shop',
            'IB_PAYER_ACC' => 'SEB0001LV',
            'IB_PAYER_NAME' => 'John Mayer',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_PAYMENT_DATE' => '10.03.2019',
            'IB_PAYMENT_TIME' => '21:12:34',
            'IB_CRC' => 'oHMsqpUTBxPVv/mRV+nOqxmcPHe3jt0w/QLO+xORyWP25bgkJpxP/afn2eUUIc6rlGrAZsTl4E0e3/12LIDurwMnYbGIyqGzBDhuzf9rB6IewiTgXuota4Yb8bcRR3EI5Dzy2WeGZxgNqH+9H+zsAWnfQlYD4NNWxj8oJwsxSIljZSsxGCpm2zvntfVs4RWGov4BrbKMh0UAbSDreweSRgk5OM7aKv/RCgkZOBhqqIeLOelRq8mYwtwp0JE3X+ahMsPuIXWWzoGrAgDYKuNonPiiRlFUnK3QTaQ/DuHExZOafxCYT3uTPBa8/uFFwbLF2Lh5DUI4DRhCEnHFIZU9Pg==',
            'IB_LANG' => 'LAT',
            'IB_FROM_SERVER' => 'Y',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Unknown IB_SERVICE code");

        $response = $this->gateway->completePurchase($this->options)->send();
    }
}
