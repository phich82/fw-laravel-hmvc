<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SmsAdapter;

class Sms implements SmsAdapter
{
    /**
     * @implement
     *
     * Send message to mobile devices (sms)
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        // Package for send sms
        Log::info('Sms sent.');
    }
}
