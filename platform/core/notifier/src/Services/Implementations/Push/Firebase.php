<?php

namespace Core\Notifier\Services\Implementations\Push;

class Firebase
{
    /**
     * @var string
     */
    private static $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

    /**
     * @var string
     */
    private static $provider = null;

    /**
     * Alias of fcm() method
     *
     * @return \Core\Notifier\Services\Implementations\Push
     */
    public static function push($data = [])
    {
        $tokens = $data['tokens'] ?? [];

        if (is_string($tokens)) {
            $tokens = [$tokens];
        }

        $notification = [
            'title' => $data['payload']['title'],
            'body'  => $data['payload']['body'],
            'icon'  => 'myIcon',
            'sound' => 'mySound'
        ];

        $fcmNotification = [
            'registration_ids' => $tokens,
            'notification' => $notification,
            'data' => $data['data']
        ];

        $headers = [
            'Authorization: key=' . env('GOOGLE_FCM_SERVER_KEY'),
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));

        $response = curl_exec($ch);

        $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $body = substr($response, $header_size);

        $curl_errno = curl_errno($ch);

        curl_close($ch);

        /*
         * 4xx status codes are client errors
         * 5xx status codes are server errors
         */
        if ($http_status_code >= 400) {
            return (object) [
                'status_code' => $http_status_code,
                'body' => json_decode($body),
                'error' => $curl_errno,
            ];
        }
        return (object) [
            'status_code' => $http_status_code,
            'body' => json_decode($body),
        ];
    }
}
