<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\SebLink\Utils\Pizza;
use Symfony\Component\HttpFoundation\ParameterBag;

class CompleteRequest extends AbstractRequest
{
    protected const ENCODING_UTF_8 = 'UTF-8';

    /**
     * @var array
     */
    protected $responseKeys = [
        'IB_SND_ID' => true,
        'IB_SERVICE' => true,
        'IB_VERSION' => true,
        'IB_PAYMENT_ID' => true,
        'IB_AMOUNT' => true,
        'IB_CURR' => true,
        'IB_REC_ID' => true,
        'IB_REC_ACC' => true,
        'IB_REC_NAME' => true,
        'IB_PAYER_ACC' => true,
        'IB_PAYER_NAME' => true,
        'IB_PAYMENT_DESC' => true,
        'IB_PAYMENT_DATE' => true,
        'IB_PAYMENT_TIME' => true,
        'IB_FROM_SERVER' => true,
        'IB_STATUS' => true,
        'IB_CRC' => false,
        'IB_LANG' => false,
    ];

    /**
     * @return mixed
     */
    public function getData()
    {
        if ($this->httpRequest->getMethod() == 'POST') {
            return $this->httpRequest->request->all();
        }

        return $this->httpRequest->query->all();
    }

    /**
     * @param array $data
     * @return CompleteResponse
     */
    public function createResponse(array $data)
    {
        // Read data from request object
        return new CompleteResponse($this, $data);
    }

    /**
     * @param $data
     * @return CompleteResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        //Validate response data before we process further
        $this->validateResponseParameters();

        // Create fake response flow
        /** @var CompleteResponse $purchaseResponseObj */
        return $this->createResponse($data);
    }

    /**
     * @throws InvalidRequestException
     */
    public function validateResponseParameters()
    {
        $response = $this->getData();
        $keys = $this->responseKeys;

        if (!isset($response['IB_SERVICE']) || !in_array($response['IB_SERVICE'], ['0003', '0004'])) {
            throw new InvalidRequestException('Unknown IB_SERVICE code');
        }

        if (!isset($response['IB_SND_ID']) || $response['IB_SND_ID'] !== 'SEBUB') {
            throw new InvalidRequestException('Invalid Bank ID');
        }

        if (!isset($response['IB_REC_ID']) || $response['IB_REC_ID'] !== $this->getMerchantId()) {
            throw new InvalidRequestException('Invalid Merchant ID');
        }

        if ($response['IB_SERVICE'] === '0003') {
            $keys['IB_FROM_SERVER'] = false;
            $keys['IB_STATUS'] = false;
        }

        //verify data corruption
        $this->validateIntegrity($keys);
    }

    /**
     * @param array $responseFields
     * @throws InvalidRequestException
     */
    protected function validateIntegrity(array $responseFields)
    {
        $responseData = new ParameterBag($this->getData());

        // Get keys that are required for control code generation
        $controlCodeKeys = array_filter($responseFields, function ($val) {
            return $val;
        });

        // Get control code required fields with values
        $controlCodeFields = array_intersect_key($responseData->all(), $controlCodeKeys);

        if (!Pizza::isValidControlCode(
            $controlCodeFields,
            $responseData->get('IB_CRC'),
            $this->getPublicCertificatePath(),
            self::ENCODING_UTF_8
        )) {
            throw new InvalidRequestException('Data is corrupt or has been changed by a third party');
        }
    }
}
