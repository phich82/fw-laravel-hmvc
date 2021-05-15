<?php

namespace Core\Notifier\Services\Implementations;

use Core\Notifier\Services\Contracts\SlackAdapter;
use Core\Notifier\Services\Contracts\NotifierContract;


class SlackNotifier implements NotifierContract
{
    /**
     * @var \Core\Notifier\Services\Contracts\NotifierContract
     */
    protected $notifier;
    /**
     * @var \Core\Notifier\Services\Contracts\SlackAdapter
     */
    protected $slack;

    /**
     * __construct
     *
     * @param  \Core\Notifier\Services\Contracts\NotifierContract $notifier
     * @param  \Core\Notifier\Services\Contracts\SlackAdapter $slack
     * @return void
     */
    public function __construct(NotifierContract $notifier, SlackAdapter $slack)
    {
        $this->notifier = $notifier;
        $this->slack = $slack;
    }

    /**
     * Send message to slack and others
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        $this->notifier->send($subject, $template, $data);
        $this->slack->send($subject, $template, $data);
    }
}
