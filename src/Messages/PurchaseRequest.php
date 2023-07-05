<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\SebLink\Utils\Pizza;

class PurchaseRequest extends AbstractRequest
{
    protected const ENCODING_UTF_8 = 'UTF-8';

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    private function getEncodedData()
    {
        $data = [
            'IB_SND_ID' => $this->getMerchantId(), // Client Agreement ID – issued by the Bank. Constant
            'IB_SERVICE' => '0002', // Request message type. Constant 0002
            'IB_VERSION' => '002', // ID of digital signature algorithm. Constant 002
            'IB_AMOUNT' => $this->getAmount(), // Payment amount
            'IB_CURR' => $this->getCurrency(), // ISO 4217 format (LVL/EUR, etc.)
            'IB_NAME' => $this->getMerchantName(), // Merchant name  (in this case: SIA Company)
            'IB_PAYMENT_ID' => $this->getTransactionReference(), // Payment order reference number
            'IB_PAYMENT_DESC' => $this->getDescription(), // Payment order description
        ];

        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    private function getDecodedData()
    {
        $data = [
            'IB_CRC' => $this->generateControlCode($this->getEncodedData()), // E‐signature hash
            'IB_FEEDBACK' => $this->getReturnUrl(), // Client URL stated in Agreement
            'IB_LANG' => $this->getLanguage(), // Communication language (LAT, ENG RUS)
        ];

        return $data;
    }

    /**
     * @param $data
     * @return string
     */
    private function generateControlCode($data)
    {
        return Pizza::generateControlCode(
            $data,
            self::ENCODING_UTF_8,
            $this->getPrivateCertificatePath(),
            $this->getPrivateCertificatePassphrase()
        );
    }

    /**
     * Glue together encoded and raw data
     *
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        return array_merge($this->getEncodedData(), $this->getDecodedData());
    }

    /**
     * @param $data
     * @return PurchaseResponse
     */
    public function sendData($data)
    {
        // Create fake response flow, so that user can be redirected
        /** @var AbstractResponse $purchaseResponseObj */
        return new PurchaseResponse($this, $data);
    }
}
