<?php

namespace Omnipay\SebLink\Messages;

class CompleteResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->getService() == '0003' && $this->isFromServer()) {
            return true;
        }

        return $this->getStatus() == 'ACCOMPLISHED';
    }

    /**
     * Checks if user has canceled transaction
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->getStatus() == 'CANCELLED';
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->data['IB_STATUS'] ?? null;
    }

    /**
     * @return bool
     */
    public function isFromServer()
    {
        return ($this->data['IB_FROM_SERVER'] ?? null) == 'Y';
    }

    /**
     * @return string|null
     */
    public function getService()
    {
        return $this->data['IB_SERVICE'] ?? null;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        if ($this->isCancelled()) {
            return 'Payment cancelled by user';
        }

        return '';
    }
}
