<?php

namespace Omnipay\SebLink\Messages;

class CompleteResponse extends AbstractResponse
{
    protected const STATUS_ACCOMPLISHED = 'ACCOMPLISHED';
    protected const STATUS_KEY = 'IB_STATUS';
    protected const STATUS_CANCELLED = 'CANCELLED';

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        if ($this->getService() == '0003') {
            return true;
        } elseif ($this->getService() == '0004' && $this->data[self::STATUS_KEY] === self::STATUS_ACCOMPLISHED) {
            return true;
        }

        return false;
    }

    /**
     * Checks if user has canceled transaction
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        if (empty($this->data[self::STATUS_KEY])) {
            return false;
        }

        return $this->data[self::STATUS_KEY] === self::STATUS_CANCELLED;
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
    public function getMessage(): string
    {
        if ($this->isCancelled()) {
            return 'Payment canceled by user';
        }

        return 'Payment was successful';
    }

    /**
     * @return bool
     */
    public function isServerToServerRequest(): bool
    {
        return ($this->data['IB_FROM_SERVER'] ?? null) == 'Y';
    }
}
