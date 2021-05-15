<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SkypeAdapter;

class Skype implements SkypeAdapter
{
    /**
     * @implement
     *
     * Send message to skype
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        // Package for send message to slack
        Log::info('Message sent to skype.');
    }
}
