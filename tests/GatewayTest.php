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
            'IB_VERSION' => '002',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_NAME' => 'some merchant',
            'IB_PAYMENT_ID' => 'abc123',
            'IB_PAYMENT_DESC' => 'purchase description',
            'IB_CRC' => 'jEWWWlkq8j6RI8bwpFjmEKwuIHD/lU0WuHBFqxk+iInXDq5IS9WSBYlXf+taUwo8/aenxe/Qqci4RfMhTeFn5+P+fX3IZnXlNKqdyoJVYEZphneWSit8QhToxLrpyvrevzq6NuRRq8znpd7wVmjeuw7Koy1cUwsPcI0OBeYKWHva8Nw76/rlW9OUexZYwwYzXoGwRkJFHF74XUXL4bgQV00/AVuwbOj0KA9Ia6kKl5jjT71JTeHOYD2F1DsRQ6oXMvMVsQdT7SB+SDllaMaJwG9ewDeY/B6Fip91LkjlV07iTOV4N0aC0ZDJdD0iO+e8ilEaD7JfV2VZP/rgQZh+nQ==',
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
            'IB_VERSION' => '002',
            'IB_AMOUNT' => '10.00',
            'IB_CURR' => 'EUR',
            'IB_NAME' => 'some merchant',
            'IB_PAYMENT_ID' => 'abc123',
            'IB_PAYMENT_DESC' => 'purchase description',
            'IB_CRC' => 'k5NqutvZ5+ECa4xQTVMUgfF/fI/EE3qNRXUs77PCpBpmIhzMCIAtlQlEVpPw4oq/222qRMUOcnq3ZPHrpD4gQUP4f3fr/7pptK/WwvdxxFMCZuCdpMJ+c4NCvDuPWIM9FHEtA2ttxB4JJ3xUwaME6C7Gua2ksSTNHY4XpuztRUmgq7EHAuZOLqd5WxEWxknBsBkclF0Uxj6AXjMxt00Tfx+QjWv7vnJf5/blSyz8NPlOjUHuDsxCuatmA0zThLyBeo3TM5mIW9V3ov/r8nIz9w0n0aLnqpR9JTF518e6yUpSgHiX3OSaEwriNAcXYLorCfMt2Ah8QwJ1b1HCCJxcXA==',
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
            'IB_VERSION' => '002',
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
            'IB_CRC' => 'qoDpJb33oBXO0uhO5rtewR9Qpk1iXzSVokB3uaUJzJhIXh7GhLC+lG0Rbxltv2A4UDmTaG1T5xlvAsBFSVh5Yd36XNZsLQjTdCUcNXR1QZSbwLipQ/KsyU2KOLycBNzHtKl34ToMRsYLuo9dyw06KB5a3QMbeDxF399o92DZInQMzMAiBz1oKyj8A/8kw4xwlrdPs/E/vpIXB/GaCfSISTu3MrQBTbS7uGbWhgvJw4wN5o54idaSOIaighZBn4Ek2ise8vQfuxqOYx2YlHjZOl+Gx+nhzS14eZVbnZ0RxYUveFct8FOgHA+vjm7VmUHhZvSzBH4IzCHilH79Osk6rQ==',
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
            'IB_VERSION' => '002',
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
            'IB_CRC' => 'qoDpJb33oBXO0uhO5rtewR9Qpk1iXzSVokB3uaUJzJhIXh7GhLC+lG0Rbxltv2A4UDmTaG1T5xlvAsBFSVh5Yd36XNZsLQjTdCUcNXR1QZSbwLipQ/KsyU2KOLycBNzHtKl34ToMRsYLuo9dyw06KB5a3QMbeDxF399o92DZInQMzMAiBz1oKyj8A/8kw4xwlrdPs/E/vpIXB/GaCfSISTu3MrQBTbS7uGbWhgvJw4wN5o54idaSOIaighZBn4Ek2ise8vQfuxqOYx2YlHjZOl+Gx+nhzS14eZVbnZ0RxYUveFct8FOgHA+vjm7VmUHhZvSzBH4IzCHilH79Osk6rQ==',
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
            'IB_VERSION' => '002',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_FROM_SERVER' => 'Y',
            'IB_STATUS' => 'ACCOMPLISHED',
            'IB_CRC' => 'p9XLVkv07Ly75kSeNnXW5hA+/4lQ3QhNAvwloyWi5l9jyhV+5h/GbYrNexId9AqTYjxt0M3PgxAoADRvqiCPyspoLyx4uka1hyWOX9Hrb61u6vPpLQP07T7L60lzbVvQEAetGPrfI9Qpd7qm0Dk+DKR1up3wTTJLk7NRuDjzgM5ciAfpqvdkgx1H+FX6HPj9QX1Fyen8OhPpsBqVGv1e0xGYA5NoRb+qkedsBOpNK7fWMKLdu4dhUV1d6qUb7f+6OJqIMAgNp8mkbHdZha3F/lCvUw43ZlSB/tUK1O6habVfMbPxV01tmifVeA96M3aKIrVFyBuJafxiipme6yY8yA==',
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
            'IB_VERSION' => '002',
            'IB_REC_ID' => 'MERCHANT1',
            'IB_PAYMENT_ID' => 'UB0000000000015',
            'IB_PAYMENT_DESC' => 'Payment for order 1231223',
            'IB_FROM_SERVER' => 'N',
            'IB_STATUS' => 'CANCELLED',
            'IB_CRC' => 'vRM1Pv3rSEfxcwszWZtSQB6cJHaGUbpYZYtn5BPfbPtukMdVUXT6TCVrzBn9mRl2K2RiJ4hUIFnTXhtDOEl/vonRyag/j5LT6E1xtLT2jdXRbErohTb3nhafZV4T48bXr7sI7aKKFpiAHiTebE+2KWRvlxMLbc95ZhAvl4WDnv1JvJpo+DisgkyFUMrsHT3LNL+Vwn+QhOSYvXRVqmgJl7bGGBCHAQo35SL452deFPcvj7B3auWYkW0UBe6g+LHdPzIiiGj8x9gACwGCIE/it5YTn7dO/tiqFVkDq0htZrV/LdtR9R5dbYa09fD2gAMHN20Bb3y5Jjrr+9TEUZeCOQ==',
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
            'IB_VERSION' => '002',
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
            'IB_VERSION' => '002',
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
