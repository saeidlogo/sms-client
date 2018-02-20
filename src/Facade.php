<?php

namespace Moontius\LaravelSMS;

use Illuminate\Support\Facades\Facade as BaseFacade;

/**
 * Facade for the SMS provider
 */
class SmsClient extends BaseFacade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sms';
    }
}
