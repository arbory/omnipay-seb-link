<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Common\Message\AbstractRequest as CommonAbstractRequest;

abstract class AbstractRequest extends CommonAbstractRequest
{
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
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * @param string $value
     */
    public function setReturnUrl($value)
    {
        $this->setParameter('returnUrl', $value);
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
     * @return string
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param string $value
     */
    public function setLanguage($value)
    {
        $this->setParameter('language', $value);
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
}