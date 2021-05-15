<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Core\Notifier\Services\Contracts\MailerAdapter;

class Mailer implements MailerAdapter
{
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
        // Package for sendmail
        // Mail::send($template, ['user' => $data], function (Message $message) use ($data) {
        //     $message
        //         ->from($data->from, $data->from_name)
        //         ->to($data->to, $data->to_name)
        //         ->subject($data->subject);
        // });
        Log::info('Email sent.');
    }
}
