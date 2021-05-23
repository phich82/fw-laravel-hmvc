<?php

namespace Core\Notifier\Services\Contracts;

interface SmsAdapter extends NotifierContract
{
    /**
     * Send message
     *
     * @param  string $to
     * @param  string $string
     * @param  array $data
     * @return void|int|bool
     */
    public function makeCall($to, $from, array $options);
}
