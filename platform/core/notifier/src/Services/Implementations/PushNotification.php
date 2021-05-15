<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\PushNotificationAdapter;

class PushNotification implements PushNotificationAdapter
{
    /**
     * @implement
     *
     * Send message to slack
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        // Package for send message to mobile devices (android, ios)
        Log::info('Message sent to mobile devices.');
    }
}
