<?php

namespace Core\Notifier\Services\Implementations\Push;

use Pushok\AuthProvider\Token;
use ricwein\PushNotification\Config;
use ricwein\PushNotification\Message;
use ricwein\PushNotification\Handler\FCM;
use ricwein\PushNotification\Handler\APNS;
use ricwein\PushNotification\PushNotification;
use ricwein\PushNotification\Result;

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
     * @return \ricwein\PushNotification\PushNotification
     */
    public function android()
    {
        return $this->fcm();
    }

    /**
     * Use FCM
     *
     * @return \ricwein\PushNotification\PushNotification
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
        $deviceTokens = array_map(fn ($deviceToken) => [$deviceToken => 'fcm'], $data['devvice_tokens']);

        return $this->fcm->send($message, $deviceTokens);
    }

    /**
     * Alias of apns() method
     *
     * @return \ricwein\PushNotification\PushNotification
     */
    public function ios()
    {
        return $this->apns();
    }

    /**
     * Use APNS
     *
     * @return \ricwein\PushNotification\PushNotification
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
            $result = $this->{"send{$PROVIDER}"}($data);
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
        return 'ExampleGooglePushToken12345678987654321';
    }

    /**
     * Get auth token from APNS
     *
     * @return \Pushok\AuthProvider\Token
     */
    private static function apnsAuthToken()
    {
        return Token::create([
            'key_id' => 'AAAABBBBCC', // The Key ID obtained from Apple developer account
            'team_id' => 'DDDDEEEEFF', // The Team ID obtained from Apple developer account
            'app_bundle_id' => 'com.app.Test', // The bundle ID for app obtained from Apple developer account
            'private_key_path' => __DIR__ . '/private_key.p8', // Path to private key
            'private_key_secret' => null // Private key secret
        ]);
    }
}
