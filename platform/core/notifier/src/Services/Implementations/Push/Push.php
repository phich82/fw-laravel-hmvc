<?php

namespace Core\Notifier\Services\Implementations\Push;

use Pushok\AuthProvider\Token;
use ricwein\PushNotification\Config;
use ricwein\PushNotification\Result;
use ricwein\PushNotification\Message;
use ricwein\PushNotification\Handler\FCM;
use ricwein\PushNotification\Handler\APNS;
use ricwein\PushNotification\PushNotification;
use ricwein\PushNotification\Exceptions\ResponseReasonException;

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

    private const FCM = 'fcm';
    private const APNS = 'apns';

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
     * @return null||array [null: success, array: error]
     */
    private function sendFCM($data = [])
    {
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);
        // Set sound, badge, priority
        if (!empty($data['sound'])) {
            $message->setSound($data['sound']);
        }
        if (!empty($data['badge']) && is_int($data['badge'])) {
            $message->setBadge($data['badge']);
        }
        if (!empty($data['priority']) && in_array($data['priority'], [Config::PRIORITY_NORMAL, Config::PRIORITY_HIGH], true)) {
            $message->setPriority($data['priority']);
        }

        $deviceTokens = array_reduce($data['device_tokens'], function ($carry, $deviceToken) {
            $carry[$deviceToken] = static::FCM;
            return $carry;
        }, []);

        return $this->_resolveResponse($this->fcm->send($message, $deviceTokens));
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
        $this->apns = new PushNotification([
            'apns' => new APNS(self::apnsAuthToken(), self::apnsEnv(), self::apnsCertPath())
        ]);

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
     * @return null|array [null: success, array: error]
     */
    private function sendAPNS($data = [])
    {
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);
        // Set sound, badge, priority
        if (!empty($data['sound'])) {
            $message->setSound($data['sound']);
        }
        if (!empty($data['badge']) && is_int($data['badge'])) {
            $message->setBadge($data['badge']);
        }
        if (!empty($data['priority']) && in_array($data['priority'], [Config::PRIORITY_NORMAL, Config::PRIORITY_HIGH], true)) {
            $message->setPriority($data['priority']);
        }

        $deviceTokens = array_map(fn ($deviceToken) => [$deviceToken => static::APNS], $data['device_tokens']);

        return $this->_resolveResponse($this->apns->send($message, $deviceTokens));
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
            static::FCM => new FCM(self::fcmAuthToken()),
            static::APNS => new APNS(self::apnsAuthToken(), self::apnsEnv(), self::apnsCertPath()),
        ]);
        $message = new Message($data['message'], $data['title'], ['payload' => $data['payload']]);
        // Set sound, badge, priority
        if (!empty($data['sound'])) {
            $message->setSound($data['sound']);
        }
        if (!empty($data['badge']) && is_int($data['badge'])) {
            $message->setBadge($data['badge']);
        }
        if (!empty($data['priority']) && in_array($data['priority'], [Config::PRIORITY_NORMAL, Config::PRIORITY_HIGH], true)) {
            $message->setPriority($data['priority']);
        }

        // Mapping device tokens to handlers
        $deviceTokensData = $data['device_tokens'];
        if (!isset($deviceTokensData['android']) && !isset($deviceTokensData['ios'])) {
            $deviceTokens = array_map(
                fn ($deviceToken) => [$deviceToken => strlen($deviceToken) === 32 ? static::APNS : static::FCM],
                $deviceTokensData
            );
        } else {
            $deviceTokens = [];
            foreach ($deviceTokensData as $key => $tokens) {
                $handler = $key == 'ios' ? static::APNS : static::FCM;
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
        return $this->_resolveResponse($push->send($message, $deviceTokens));
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
     * @return string
     */
    private static function apnsAuthToken()
    {
        return Token::create([
            'key_id' => env('APPLE_KEY_ID'), // The Key ID obtained from Apple developer account
            'team_id' => env('APPLE_TEAM_ID'), // The Team ID obtained from Apple developer account
            'app_bundle_id' => env('APPLE_APP_BUNDLE_ID'), // The bundle ID for app obtained from Apple developer account
            'private_key_path' => env('APPLE_PRIVATE_KEY_PATH'), // Path to private key (private_key.p8)
            'private_key_secret' => env('APPLE_PRIVATE_KEY_SECRECT') // Private key secret
        ]);
    }

    /**
     * Get apns cetificate path
     *
     * @return string
     */
    private static function apnsCertPath()
    {
        return storage_path(env('APPLE_CERT_STORAGE_PATH'));
    }

    /**
     * Get apns enviroment
     *
     * @return string
     */
    private static function apnsEnv()
    {
        switch (env('APP_ENV')) {
            case 'prod':
            case 'production':
                return Config::ENV_PRODUCTION;
            default:
                return Config::ENV_DEVELOPMENT;
        }
    }

    /**
     * Resolve the response from pushing notification
     *
     * @param \ricwein\PushNotification\Result $result
     * @return null|array
     */
    private function _resolveResponse($result)
    {
        if (is_object($result)) {
            $errors = [];
            // Get device tokens failed
            $failedDeviceTokens = $result->getInvalidDeviceTokes();
            foreach ($result->getFailed() as $token => $error) {
                $errors[$token] = $error;
                if ($error instanceof ResponseReasonException) {
                    if ($error->isInvalidDeviceToken()) {
                        // $token was invalid
                        $errors[$token] = $error;
                    } elseif ($error->isRateLimited()) {
                        // the $token device got too many notifications and is currently rate-limited => better wait some time before sending again.
                        $errors[$token] = $error;
                    }
                }
            }
            return $errors;
        }
        return null;
    }
}
