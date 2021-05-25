<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Core\Notifier\Services\Contracts\MailerAdapter;
use Exception;

class Mailer extends BaseNotifier implements MailerAdapter
{
    /**
     * @var string
     */
    protected $provider = 'Gmail';

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

        $success = [];
        $failed  = [];

        try {
            Mail::raw($body, function (Message $message) use ($subject, $fromAddress, $fromName, $listTo, $listCc, $listBcc, &$success) {
                $message->subject($subject);
                $message->from($fromAddress, $fromName);

                foreach ($listTo as $to) {
                    $to = !is_array($to) ? [$to] : $to;
                    $success[] = $to;
                    $message->to(...$to);
                }
                foreach ($listCc as $cc) {
                    $cc = !is_array($cc) ? [$cc] : $cc;
                    $success[] = $cc;
                    $message->cc(...$cc);
                }
                foreach ($listBcc as $bcc) {
                    $bcc = !is_array($bcc) ? [$bcc] : $bcc;
                    $success[] = $bcc;
                    $message->bcc(...$bcc);
                }
            });
            // Log::info("[{$this->provider}][{$to}][Send] => success");
        } catch (Exception $e) {
            // $failed[] = $to;
            // Log::error(__CLASS__.':'.__FUNCTION__."[{$to}][Error] => {$e->getMessage()}");
            Log::error("[{$this->provider}][".__CLASS__.':'.__FUNCTION__."][Error] => {$e->getMessage()}");
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
    }
}
