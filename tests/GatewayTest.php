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
            'IB_CRC' => 's+oX9U0cMu53QldGpZw43qfagFpoEx69ZxDMrfw7slRAupvwuJC15EmUN6qYdbJywMsRuCqHnjYLz7QSuLUtrv84AV/Q7vfpnpcbz9QldwGcC8HtEhT40QsmcICdiabyHBtWnsbnlwQcpNBOen/cfutRVPx+lRVJZDArm4gd0owUdmjHkw7l91CWj5p/XALBQvOb27MLV4P2W/3gxZxCZMQbsYSP7YMFou3FX5FiaxONRESeJWUNVBk/pT1FhylzgMBwW1R06n5fOUF2KDFI0QnaQrEFLM6BJ/oKSZshUd8HDq+UMEhYrbQzXtJQ0AzApzLpuT3ui+lVDuzoZKQkbw==',
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
            'IB_CRC' => 'OAFJCSE0y3QWXYrWsi1/XbtmNT7MGlm2c+5LFdBXlwHWnMelNJFYdqq7Wc45Z8WwGht7y3GrTqhM0zWycMwhz5oC7OrdSKsBKyOYq6Znvn+sUubCV7tIr5B0trekWEvlATGFX5cTxP/GNCEyAIiQyBVv6G5pz37a0BHQ0vedqK9nz7MNGmHFF7+X7Itv5G+jCtMUYN1gLh/fBZ/osSAKJKEjF5TvpvVIaZBo4I2nAF9fVULubPjCpnUZVbQzVRcApwcfWFzMwZroVJwFVr1ENiNc2jUdbPnM+FKqVjil1Dmp3cXizyFCZ3VlMiyMYtYzb3BFJLz2oCsR13mUA8zFiw==',
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
            'IB_CRC' => 'oTNCjRUlI/Lvt0K3Rf2ANvwBrnJrQ15RXNlQ3uw2Q54m/G+stNmP83/fuACHzKDr9QVcIA3aPNdpzIO2NZ8aTL83a3bCW/RY3GX73ufZZ1yrxjuxOT5xBIH/Rv+BMTOi1XrsGBLFDyFI19ZdGS5kzRvdW7jBrRa2wIm9jIIURJTeWAyujho+aqMf/dxQhcIcNHyErpZBy3FHfAAt2KiUL/tuayoBzt75FZh3BMaDE8jEx/NblvSNKhkBL9WkPZcSLwVEZZ1oRF7A8v4lLRxvuvxEeFZo8UV5e7sgQGkQLHbnVhOmbgr59AwaSQfnP3CSzONHDw0S6IzVwQEjr+7N7w==',
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
            'IB_CRC' => 'oTNCjRUlI/Lvt0K3Rf2ANvwBrnJrQ15RXNlQ3uw2Q54m/G+stNmP83/fuACHzKDr9QVcIA3aPNdpzIO2NZ8aTL83a3bCW/RY3GX73ufZZ1yrxjuxOT5xBIH/Rv+BMTOi1XrsGBLFDyFI19ZdGS5kzRvdW7jBrRa2wIm9jIIURJTeWAyujho+aqMf/dxQhcIcNHyErpZBy3FHfAAt2KiUL/tuayoBzt75FZh3BMaDE8jEx/NblvSNKhkBL9WkPZcSLwVEZZ1oRF7A8v4lLRxvuvxEeFZo8UV5e7sgQGkQLHbnVhOmbgr59AwaSQfnP3CSzONHDw0S6IzVwQEjr+7N7w==',
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
            'IB_CRC' => 'OVFY5JD6Oj767KVBMHCGgc1KTkUu1U24WVECsAeHsVay0w899odO5qvF7fBHdJNcwvKg6/OgD54pUfzjuM0bFzWc5qMEoAdtSqKV/SNRLZLb4z1rkGPvqcUWoY5vnAPlZOruwoIzLCQpoigqPZ56jyNMHvv9t38JAKVzUfpBs6DawXsgsKaMyvUIbaBttBi6rZUkvN2JBihx1bLLqz78HmR48Tp8d9bnI3qAozpDERNLczwdEqFqXMWNn/C0u8xqi7ZsAHxLeOz2F10oGkIhQX/SRuf/G2SRH82PgtpvFOQSo3/gsviY5VASvxfHK76JNnlYAIP2QDhdrOe4DiQbhQ==',
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
            'IB_CRC' => 'qAEgqIRVUMnr5EmSJV3LILayEGF/7cU4c2ZvDD8y9OK4RufUnF3XahxHZzGmSzYlRTEiHXiNRnv+JAq3ygj5xlOaGrF7+eCIY45SzcmT+TQVZxFdOHrFIv6rfI9HNpEoNT/r/0sER8xvIR2/n6TSRvWy/Q/BPtm+8tVztK61OLdjXOVbOH4KzpaZAwsP1mqaMtOx89O3EV3Dla/Z+BNKJqWs++FJcERcANNSUKlXxeoqT+elVmB1uG7G3UJB6x5dd03V/fYUY+aK4A4+vjHm4Dzcnwi6W1GwvF9U646obOJ2ge1uhed6QJbYI8a9Igz4iLetNQfXNAxsulEA22nkkw==',
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
            'IB_CRC' => 'oTNCjRUlI/Lvt0K3Rf2ANvwBrnJrQ15RXNlQ3uw2Q54m/G+stNmP83/fuACHzKDr9QVcIA3aPNdpzIO2NZ8aTL83a3bCW/RY3GX73ufZZ1yrxjuxOT5xBIH/Rv+BMTOi1XrsGBLFDyFI19ZdGS5kzRvdW7jBrRa2wIm9jIIURJTeWAyujho+aqMf/dxQhcIcNHyErpZBy3FHfAAt2KiUL/tuayoBzt75FZh3BMaDE8jEx/NblvSNKhkBL9WkPZcSLwVEZZ1oRF7A8v4lLRxvuvxEeFZo8UV5e7sgQGkQLHbnVhOmbgr59AwaSQfnP3CSzONHDw0S6IzVwQEjr+7N7w==',
            'IB_LANG' => 'LAT',
            'IB_FROM_SERVER' => 'Y',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage("Unknown IB_SERVICE code");

        $response = $this->gateway->completePurchase($this->options)->send();
    }
}
