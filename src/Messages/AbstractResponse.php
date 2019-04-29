<?php

namespace Omnipay\SebLink\Messages;

use Omnipay\Common\Message\AbstractResponse as CommonAbstractResponse;

abstract class AbstractResponse extends CommonAbstractResponse
{
    /**
     * @return null|string
     */
    public function getTransactionReference()
    {
        return $this->data['IB_PAYMENT_ID'] ?? $this->data['IB_PAYMENT_ID'];
    }
}
