<?php

namespace Omnipay\SebLink;

use Omnipay\Common\AbstractGateway;
use Omnipay\SebLink\Messages\PurchaseRequest;
use Omnipay\SebLink\Messages\CompleteRequest;

/**
 * Class Gateway
 *
 * @package Omnipay\SebLink
 */
class Gateway extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'SEB Link';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'gatewayUrl' => 'https://ibanka.seb.lv/ipc/epakindex.jsp',
            'merchantId' => '',
            'merchantBankAccount' => '',
            'merchantName' => '',
            'returnUrl' => '',
            'privateCertificatePath' => '',
            'privateCertificatePassword' => null,
            'publicCertificatePath' => '',

            //Global parameters for requests will be set via gateway
            'language' => 'LAT',
        );
    }


    /**
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $options = [])
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * Complete transaction
     *
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function completePurchase(array $options = [])
    {
        return $this->createRequest(CompleteRequest::class, $options);
    }

    /**
     * @param string $value
     */
    public function setGatewayUrl($value)
    {
        $this->setParameter('gatewayUrl', $value);
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->getParameter('gatewayUrl');
    }

    /**
     * @param string $value
     */
    public function setMerchantId($value)
    {
        $this->setParameter('merchantId', $value);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param string $value
     */
    public function setMerchantName($value)
    {
        $this->setParameter('merchantName', $value);
    }

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->getParameter('merchantName');
    }

    /**
     * @param string $value
     */
    public function setReturnUrl($value)
    {
        $this->setParameter('returnUrl', $value);
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * @param string $value
     */
    public function setPrivateCertificatePath($value)
    {
        $this->setParameter('privateCertificatePath', $value);
    }

    /**
     * @return string
     */
    public function getPrivateCertificatePath()
    {
        return $this->getParameter('privateCertificatePath');
    }

    /**
     * @param string $value
     */
    public function setPrivateCertificatePassword($value)
    {
        $this->setParameter('privateCertificatePassword', $value);
    }

    /**
     * @return string
     */
    public function getPrivateCertificatePassword()
    {
        return $this->getParameter('privateCertificatePassword');
    }

    /**
     * @param string $value
     */
    public function setPublicCertificatePath($value)
    {
        $this->setParameter('publicCertificatePath', $value);
    }

    /**
     * @return string
     */
    public function getPublicCertificatePath()
    {
        return $this->getParameter('publicCertificatePath');
    }

    /**
     * @param string $value
     */
    public function setLanguage($value)
    {
        $this->setParameter('language', $value);
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }
}