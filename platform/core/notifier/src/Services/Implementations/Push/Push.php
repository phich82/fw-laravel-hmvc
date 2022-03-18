<?php

namespace Core\Notifier\Services\Implementations\Push;

use Pushok\AuthProvider\Token;
use ricwein\PushNotification\Config;
use ricwein\PushNotification\Result;
use ricwein\PushNotification\Message;
use ricwein\PushNotification\Handler\FCM;
use ricwein\PushNotification\Handler\APNS;
use ricwein\PushNotification\PushNotification;

class Push
{
    /**
     * @var \ricwein\PushNotification\PushNotification
     */
    private $fcm;

    /**
     * @var \ricwein\PushNotification\PushNotification
     */
    private $apns;

    /**
     * @var string
     */
    private $provider = null;

    /**
     * Alias of fcm() method
     *
     * @return \Core\Notifier\Services\Implementations\Push
     */
    public function android()
    {
        return $this->fcm();
    }

    /**
     * Use FCM
     *
     * @return \Core\Notifier\Services\Implementations\Push
     */
    public function fcm()
    {
        $this->provider = 'fcm';
        $this->fcm = new PushNotification(['fcm' => new FCM(self::fcmAuthToken())]);

        return $this;
    }

    /**
     * Send notification by FCM
     *
     * @param  array $data [
     *      'message' => <string>,
     *      'title'   => <string>,
     *      'payload' => <array>,
     *      'devvice_tokens' => <array>
     * ]
     * @return \ricwein\PushNotification\Result
     */
    private function sendFCM($data = [])
    {
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);
        $deviceTokens = array_reduce($data['device_tokens'], function ($carry, $deviceToken) {
            $carry[$deviceToken] = 'fcm';
            return $carry;
        }, []);

        return $this->fcm->send($message, $deviceTokens);
    }

    /**
     * Alias of apns() method
     *
     * @return \Core\Notifier\Services\Implementations\Push
     */
    public function ios()
    {
        return $this->apns();
    }

    /**
     * Use APNS
     *
     * @return \Core\Notifier\Services\Implementations\Push
     */
    public function apns($data = [])
    {
        $this->provider = 'apns';
        $this->apns = new PushNotification(['apns' => new APNS(self::apnsAuthToken(), Config::ENV_PRODUCTION)]);

        return $this;
    }

    /**
     * Send notification by APNS
     *
     * @param  array $data [
     *      'message' => <string>,
     *      'title'   => <string>,
     *      'payload' => <array>
     * ]
     * @return \ricwein\PushNotification\Result
     */
    private function sendAPNS($data = [])
    {
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);
        $deviceTokens = array_map(fn ($deviceToken) => [$deviceToken => 'apns'], $data['device_tokens']);

        return $this->apns->send($message, $deviceTokens);
    }

    /**
     * Send notification to mobile devices
     *
     * @param  array $data
     * @return \ricwein\PushNotification\Result
     */
    public function send($data = [])
    {
        if ($this->provider) {
            $PROVIDER = strtoupper($this->provider);
            $send = "send{$PROVIDER}";
            $result = $this->{$send}($data);
            $this->provider = null;

            return $result;
        }

        $push = new PushNotification([
            'fcm' => new FCM(self::fcmAuthToken()),
            'apns' => new APNS(self::apnsAuthToken(), Config::ENV_PRODUCTION),
        ]);
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);

        // Mapping device tokens to handlers
        $deviceTokensData = $data['device_tokens'];
        if (!isset($deviceTokensData['android']) && !isset($deviceTokensData['ios'])) {
            $deviceTokens = array_map(
                fn ($deviceToken) => [$deviceToken => strlen($deviceToken) === 32 ? 'apns' : 'fcm'],
                $deviceTokensData
            );
        } else {
            $deviceTokens = [];
            foreach ($deviceTokensData as $key => $tokens) {
                $handler = $key == 'ios' ? 'apns' : 'fcm';
                foreach ($tokens as $token) {
                    $deviceTokens[$token] = $handler;
                }
            }
        }
        // [
        //     '<ios-device-token1>' => 'apns',
        //     '<ios-device-token2>' => 'apns',
        //     '<android-device-token1>' => 'fcm',
        //     '<android-device-token2>' => 'fcm',
        // ]
        return $push->send($message, $deviceTokens);
    }

    /**
     * Get auth token from FCM
     *
     * @return string
     */
    private static function fcmAuthToken()
    {
        return env('GOOGLE_FCM_SERVER_KEY');
    }

    /**
     * Get auth token from APNS
     *
     * @return \Pushok\AuthProvider\Token
     */
    private static function apnsAuthToken()
    {
        return Token::create([
            'key_id' => env('APPLE_KEY_ID'), // The Key ID obtained from Apple developer account
            'team_id' => env('APPLE_TEAM_ID'), // The Team ID obtained from Apple developer account
            'app_bundle_id' => env('APPLE_APP_BUNDLE_ID'), // The bundle ID for app obtained from Apple developer account
            'private_key_path' => env('APPLE_PRIVATE_KEY_PATH'), // Path to private key
            'private_key_secret' => env('APPLE_PRIVATE_KEY_SECRECT') // Private key secret
        ]);
    }
}
