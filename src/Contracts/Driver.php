<?php

namespace Moontius\LaravelSMS\Contracts;

use Moontius\LaravelSMS\SmsResult;

interface Driver {

    /**
     * Get driver name.
     *
     * @return string
     */
    public function getDriver(): string;

    /**
     * Get endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string;

    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @return boolean
     */
    public function sendRequest(array $message): SmsResult;
}
