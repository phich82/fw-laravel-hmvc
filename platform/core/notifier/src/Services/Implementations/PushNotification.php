<?php

namespace Core\Notifier\Services\Implementations;

use Exception;
use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Implementations\Push\Push;
use Core\Notifier\Services\Contracts\PushNotificationAdapter;

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
     * Send notification to mobile devices
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

        $successDeviceTokens = [];
        $failedDeviceTokens  = [];

        // Package for send message to mobile devices (android, ios)
        try {
            $result = (new Push)->send($data);

            // Get device tokens failed
            $failedDeviceTokens = is_array($result) && count($result) > 0 ? array_keys($result) : [];
            $deviceTokens = $data['device_tokens'];
            if (isset($deviceTokens['ios']) && isset($deviceTokens['android'])) {
                $deviceTokens = array_merge($deviceTokens['ios'], $deviceTokens['android']);
            }
            $successDeviceTokens = array_diff($deviceTokens, $failedDeviceTokens);
        } catch (Exception $e) {
            Log::error("[{$this->provider}][".__CLASS__.':'.__FUNCTION__."][Error] => {$e->getMessage()}");
        }

        if (!empty($failedDeviceTokens)) {
            Log::info("[{$this->provider}][Tokens Failed] => ".json_encode_pretty($failedDeviceTokens));
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failedDeviceTokens));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($successDeviceTokens));

        return count($successDeviceTokens);
    }
}
