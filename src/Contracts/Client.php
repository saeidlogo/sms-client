<?php

namespace Moontius\LaravelSMS\Contracts;

/**
 * SMS client.
 */
use Moontius\LaravelSMS\SmsResult;

interface Client {

    /**
     * Get the driver name.
     *
     * @return string
     */
    public function getDriver(): string;

    /**
     * Send the message.
     *
     * @param array $msg The message array.
     *
     * @return boolean
     */
    public function send(array $msg): SmsResult;
}
