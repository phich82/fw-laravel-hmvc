<?php

namespace Core\Notifier\Services\Implementations;

use Core\Notifier\Services\Contracts\SkypeAdapter;
use Core\Notifier\Services\Contracts\NotifierContract;

class SkypeNotifier implements NotifierContract
{
    /**
     *
     * @var \Core\Notifier\Services\Contracts\NotifierContract
     */
    protected $notifier;
    /**
     * @var \Core\Notifier\Services\Contracts\SkypeAdapter
     */
    protected $skype;

    /**
     * __construct
     *
     * @param  \Core\Notifier\Services\Contracts\NotifierContract $notifier
     * @param  \Core\Notifier\Services\Contracts\SkypeAdapter $skype
     * @return void
     */
    public function __construct(NotifierContract $notifier, SkypeAdapter $skype)
    {
        $this->notifier = $notifier;
        $this->skype = $skype;
    }

    /**
     * Send message to skype and others
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|int|bool
     */
    public function send($subject, $template, $data)
    {
        $this->notifier->send($subject, $template, $data);
        $this->skype->send($subject, $template, $data);
    }
}
