<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Common\Message\AbstractResponse as CommonAbstractResponse;

abstract class AbstractResponse extends CommonAbstractResponse
{
    /**
     * @return string
     */
    public function getMessage()
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getTransactionReference()
    {
        $data = $this->getData();
        return $data['IB_PAYMENT_ID'] ?? $data['IB_PAYMENT_ID'];
    }
}
