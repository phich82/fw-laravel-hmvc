<?php

// require './vendor/autoload.php';
namespace Core\Notifier\Services\Implementations\Push;

use Minishlink\WebPush\VAPID;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\MessageSentReport;

class WebPush
{
    /**
     * @var string
     */
    private $provider = 'browser';

    /**
     * Send notifications to web browser
     *
     * @param array $data
     * @return null|array
     */
    public function send($data = [])
    {
        $webpush = new \Minishlink\WebPush\WebPush(self::_authConfig(), self::_defaultOptions());
        $subscription = Subscription::create($data['subscription']);

        return $this->_resolveResponse($webpush->sendOneNotification(
            $subscription,
            $this->_resolvePayload($data['payload'])
        ));
    }

    /**
     * Get auth config
     *
     * @return array
     */
    private static function _authConfig()
    {
        return [
            'VAPID' => [
                'subject'    => '',
                'publicKey'  => env('VAPID_PUBLIC_KEY'), // don't forget
                'privateKey' => env('VAPID_PRIVATE_KEY'), // in the real world, this would be in a secret file
                // 'subject' => 'mailto:me@website.com', // can be a mailto: or your website address
                // 'pemFile' => 'path/to/pem', // if you have a PEM file and can link to it on your filesystem
                // 'pem'     => 'pemFileContent', // if you have a PEM file and want to hardcode its content
            ]
        ];
    }

    /**
     * Get the default options
     *
     * @return array
     */
    private static function _defaultOptions()
    {
        return [
            /**
             * Time To Live (TTL, in seconds) is how long a push message is retained by the push service (eg. Mozilla)
             * in case the user browser is not yet accessible (eg. is not connected). You may want to use a very long
             * time for important notifications. The default TTL is 4 weeks. However, if you send multiple nonessential
             * notifications, set a TTL of 0: the push notification will be delivered only if the user is currently
             * connected. For other cases, you should use a minimum of one day if your users have multiple time zones,
             * and if they don't several hours will suffice.
             */
            'TTL' => 300, // defaults to 4 weeks
            /**
             * Urgency can be either "very-low", "low", "normal", or "high". If the browser vendor has implemented
             * this feature, it will save battery life on mobile devices (cf. protocol).
             */
            'urgency' => 'normal', // protocol defaults to "normal"
            /**
             * Similar to the old collapse_key on legacy GCM servers, this string will make the vendor show to the
             * user only the last notification of this topic (cf. protocol).
             */
            'topic' => 'new_event', // not defined by default,
            /**
             * If you send tens of thousands notifications at a time, you may get memory overflows due to how
             * endpoints are called in Guzzle. In order to fix this, WebPush sends notifications in batches.
             * The default size is 1000. Depending on your server configuration (memory), you may want to
             * decrease this number. Do this while instanciating WebPush or calling setDefaultOptions. Or,
             * if you want to customize this for a specific flush, give it as a parameter: $webPush->flush($batchSize).
             */
            'batchSize' => 500, // defaults to 1000
        ];
    }

    /**
     * Resolve payload of notification
     *
     * @param string|array $payload
     * @return string [json string]
     */
    private function _resolvePayload($payload)
    {
        if (!is_array($payload)) {
            $payload = ['body' => $payload];
        }
        return json_encode(array_merge([
            "title" => "Title",
            "body"  => "",
            "icon"  => "https://picsum.photos/64",
            "image" => "https://picsum.photos/200",
            "badge" => "https://picsum.photos/32",
            "sound" => "http://commondatastorage.googleapis.com/codeskulptor-demos/DDR_assets/Kangaroo_MusiQue_-_The_Neverwritten_Role_Playing_Game.mp3",
            "lang"  => 'en-US',
            "tag"   => null,
            "data"  => null,
            "vibrate" => true,
            "requireInteraction" => false,
        ], $payload));
    }

    /**
     * Resolve the response from pushing notification
     *
     * @param \Minishlink\WebPush\MessageSentReport $result
     * @return null|array
     */
    private function _resolveResponse($result)
    {
        // handle eventual errors here, and remove the subscription from your server if it is expired
        $endpoint = $result->getRequest()->getUri()->__toString();

        if ($result->isSuccess()) {
            //echo "[v] Message sent successfully for subscription {$endpoint}.";
            return null;
        }
        //echo "[x] Message failed to sent for subscription {$endpoint}: {$result->getReason()}";
        return [
            $endpoint => $result->getReason()
        ];
    }
}

// var_dump(VAPID::createVapidKeys());
