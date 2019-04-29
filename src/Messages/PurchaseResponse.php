<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return false;
    }

    /**
     * Redirect is processed from merchants HTML form by auto-submitting it to gateway
     * Use this flag if you want to render custom redirect form
     * https://github.com/thephpleague/omnipay/issues/306
     *
     * @return bool
     */
    public function isTransparentRedirect()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'POST';
    }

    /**
     * @return array|mixed
     */
    public function getRedirectData()
    {
        return $this->getData();
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        /** @var AbstractRequest $request */
        $request = $this->getRequest();
        return $request->getGatewayUrl();
    }
}
