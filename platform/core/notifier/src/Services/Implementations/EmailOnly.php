<?php

namespace Core\Notifier\Services\Implementations;

use Core\Notifier\Services\Contracts\MailerAdapter;
use Core\Notifier\Services\Contracts\NotifierContract;


class EmailOnly implements NotifierContract
{
    /**
     * @var \Core\Notifier\Services\Contracts\MailerAdapter
     */
    protected $mailer;

    /**
     * __construct
     *
     * @param  \Core\Notifier\Services\Contracts\MailerAdapter $mailer
     * @return void
     */
    public function __construct(MailerAdapter $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @implement
     *
     * Sendmail
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        $this->mailer->send($subject, $template, $data);
    }
}
