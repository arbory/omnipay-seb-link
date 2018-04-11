<?php

namespace Omnipay\SebLink\Messages;

class CompleteResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->data['IB_STATUS'] == 'ACCOMPLISHED') {
            return true;
        }
        return false;
    }

    /**
     * Checks if user has canceled transaction
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->data['IB_STATUS'] == 'CANCELLED';
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if ($this->data['IB_STATUS'] == 'CANCELLED') {
            return 'Paymant canceled by user';
        }
        return 'Unknown gateways error';
    }
}