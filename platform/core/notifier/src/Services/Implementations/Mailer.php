<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Core\Notifier\Services\Contracts\MailerAdapter;
use Exception;

class Mailer implements MailerAdapter
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * __construct
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @implement
     *
     * Sendmail
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|int|bool
     */
    public function send($subject, $template, $data)
    {
        $body = $template;
        if (!empty($this->data)) {
            $data = $this->data;
            $body = $data['body'] ?? $template;
            $subject = $data['subject'] ?? $subject;
        }

        // Package for sendmail
        // Mail::send($template, ['user' => $data], function (Message $message) use ($data) {
        //     $message
        //         ->from($data->from, $data->from_name)
        //         ->to($data->to, $data->to_name)
        //         ->subject($data->subject);
        // });

        $listTo = $data['to'] ?? [];
        $listCc = $data['cc'] ?? [];
        $listBcc = $data['bcc'] ?? [];
        $fromAddress = $data['from'] ?? env('MAIL_FROM_ADDRESS');
        $fromName = $data['from_name'] ?? env('MAIL_FROM_NAME');

        try {
            Mail::raw($body, function (Message $message) use ($subject, $fromAddress, $fromName, $listTo, $listCc, $listBcc) {
                $message->subject($subject);
                $message->from($fromAddress, $fromName);

                foreach ($listTo as $to) {
                    $to = !is_array($to) ? [$to] : $to;
                    $message->to(...$to);
                }
                foreach ($listCc as $cc) {
                    $cc = !is_array($cc) ? [$cc] : $cc;
                    $message->cc(...$cc);
                }
                foreach ($listBcc as $bcc) {
                    $bcc = !is_array($bcc) ? [$bcc] : $bcc;
                    $message->bcc(...$bcc);
                }
            });
            Log::info('All emails sent.');
            return true;
        } catch (Exception $e) {
            Log::error(__CLASS__.':'.__FUNCTION__."[Error] => {$e->getMessage()}");
        }
        return false;
    }
}
