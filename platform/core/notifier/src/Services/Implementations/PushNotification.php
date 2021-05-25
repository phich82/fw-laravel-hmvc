<?php

namespace Core\Notifier\Services\Implementations;

use Exception;
use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Implementations\Push\Push;
use Core\Notifier\Services\Contracts\PushNotificationAdapter;
use ricwein\PushNotification\Exceptions\ResponseReasonException;

class PushNotification extends BaseNotifier implements PushNotificationAdapter
{
    /**
     * Provider name
     *
     * @var string
     */
    protected $provider = 'Push';

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
        if (!empty($this->data)) {
            $data = $this->data;
        }

        $success = [];
        $failed  = [];

        // Package for send message to mobile devices (android, ios)
        try {
            $result = (new Push)->send($data);

            // Get device tokens failed
            $failed = $result->getInvalidDeviceTokes();
            $deviceTokens = $data['device_tokens'];
            if (isset($deviceTokens['ios']) && isset($deviceTokens['android'])) {
                $deviceTokens = array_merge($deviceTokens['ios'], $deviceTokens['android']);
            }
            $success = array_diff($deviceTokens, $failed);
        } catch (Exception $e) {
            Log::error("[{$this->provider}][".__CLASS__.':'.__FUNCTION__."][{$currentSkypeId}][Error] => {$e->getMessage()}");
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
    }
}
