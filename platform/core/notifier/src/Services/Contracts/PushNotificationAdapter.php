<?php

namespace Core\Notifier\Services\Contracts;

interface PushNotificationAdapter
{
    /**
     * Send message
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|int|bool
     */
    public function send($subject, $template, $data);
}
