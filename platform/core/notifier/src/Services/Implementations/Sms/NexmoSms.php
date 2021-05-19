<?php

namespace Core\Notifier\Services\Implementations\Sms;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SmsAdapter;
use Exception;

class NexmoSms implements SmsAdapter
{
    /**
     * @var \Twilio\Rest\Client
     */
    private $client;

    /**
     * Provider name
     *
     * @var string
     */
    private $provider = 'Nexmo';

    /**
     *
     * @var mixed
     */
    private $data;

    /**
     * __construct
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $apiKey = env('NEXMO_API_KEY'); // Your Account SID from www.twilio.com/console
        $secret = env('NEXMO_SECRET_KEY'); // Your Auth Token from www.twilio.com/console

        if (!$this->client) {
            $basic  = new \Vonage\Client\Credentials\Basic($apiKey, $secret);
            $this->client = new \Vonage\Client($basic, [
                'base_api_url' => 'rest.nexmo.com'
            ]);
        }

        $this->data = $data;
    }

    /**
     * Send meesage to mobile devices
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void
     */
    public function send($subject, $template, $data)
    {
        if (!empty($this->data)) {
            $data = $this->data;
        }

        if (empty($data['phone_number'])) {
            Log::info("[{$this->provider}][Send] => Phone number is empty.");
            return null;
        }

        if (!is_array($data['phone_number'])) {
            $data['phone_number'] = [$data['phone_number']];
        }

        $messages = [];

        foreach ($data['phone_number'] as $phoneNumber) {
            $text = new \Vonage\SMS\Message\SMS(
                $phoneNumber,
                $data['from'] ?? env('NEXMO_FROM'),
                $subject ?? $data['message'] ?? ''
            );

            $response = $this->client->sms()->send($text);

            $message = $response->current();

            $messages[] = (array) $message;

            Log::info("[{$this->provider}][{$phoneNumber}][Send][Result] => ".json_encode_pretty((array) $message));

            if ($message->getStatus() == 0) {
                Log::info("[{$this->provider}][{$phoneNumber}][Send] => success");
            } else {
                Log::error(json_encode_pretty((array) $message));
            }
        }

        Log::info("[{$this->provider}][Result] => ".json_encode_pretty($messages));
        Log::info("[{$this->provider}][Send] => All sent.");

        return $messages;
    }

    /**
     * Make call
     *
     * @param  string $to
     * @param  string $from
     * @param  array $extra
     * @return void
     */
    public function makeCall($to, $from, array $options = [])
    {
        //
    }
}
