<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Pusher\PushNotifications\PushNotifications;
use Core\Notifier\Services\Contracts\PusherAdapter;

class Pusher implements PusherAdapter
{
    /**
     * @implement
     *
     * Send message to slack
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|int|bool
     */
    public function send($subject, $template, $data)
    {
        // Package for send message to pusher server
        $pusher = new PushNotifications([
            'instanceId' => env('PUSHER_APP_ID'),
            'secretKey' => env('PUSHER_APP_SECRET'),
        ]);

        $deviceTokens = $data['device_tokens'] ?? [];

        $reponse = $pusher->publishToInterests($deviceTokens, $this->configRequest($data));

        dd($reponse);

        Log::info('Message sent to pusher: '.json_encode($reponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Config for equest
     *
     * @param array $data
     * @return array
     */
    private function configRequest($data)
    {
        return [
            'fcm' => [
                'notification' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                ],
                'data' => [
                    'test' => 'test'
                ],
            ],
            'apns' => [
                'aps' => [
                    "content-available" => 1, // background update notification
                    'alert' => [
                        'title' => $data['title'],
                        'body' => $data['body'],
                    ],
                    "badge" => 9,
                    "sound" => "sound.mp3",
                ],
            ],
            'web' => [
                'notification' => [
                    'title' => $data['title'],
                    'body' => $data['body'],
                ],
            ],
        ];
    }
}
