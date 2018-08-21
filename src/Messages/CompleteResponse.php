<?php

namespace Omnipay\SebLink\Messages;

class CompleteResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        if (empty($this->data['IB_STATUS'])) {
            return false;
        }
        return $this->data['IB_STATUS'] == 'ACCOMPLISHED';
    }

    /**
     * Checks if user has canceled transaction
     *
     * @return bool
     */
    public function isCancelled()
    {
        if (empty($this->data['IB_STATUS'])) {
            return false;
        }
        return $this->data['IB_STATUS'] == 'CANCELLED';
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if (!empty($this->data['IB_STATUS']) && $this->data['IB_STATUS'] == 'CANCELLED') {
            return 'Paymant canceled by user';
        }
        return 'Unknown gateways error';
    }
}