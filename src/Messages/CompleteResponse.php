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
        if (isset($this->data['IB_STATUS'])) {
            return $this->data['IB_STATUS'];
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isFromServer()
    {
        if (isset($this->data['IB_FROM_SERVER'])) {
            return $this->data['IB_FROM_SERVER'] == 'Y';
        }

        return false;
    }

    /**
     * @return string|null
     */
    public function getService()
    {
        if (isset($this->data['IB_SERVICE'])) {
            return $this->data['IB_SERVICE'];
        }

        return null;
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
