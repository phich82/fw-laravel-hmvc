<?php

namespace Core\Notifier\Services\Implementations;

use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Contracts\NotifierContract;


class SmsNotifier implements NotifierContract
{
    /**
     * @var \Core\Notifier\Services\Contracts\NotifierContract
     */
    protected $notifier;
    /**
     * @var \Core\Notifier\Services\Contracts\SmsAdapter
     */
    protected $sms;

    /**
     * __construct
     *
     * @param  \Core\Notifier\Services\Contracts\NotifierContract $notifier
     * @param  \Core\Notifier\Services\Contracts\SmsAdapter $sms
     * @return void
     */
    public function __construct(NotifierContract $notifier, SmsAdapter $sms)
    {
        $this->notifier = $notifier;
        $this->sms = $sms;
    }

    /**
     * Send message to mobile devices
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        $this->notifier->send($subject, $template, $data);
        $this->sms->send($subject, $template, $data);
    }
}
