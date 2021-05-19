<?php

namespace Core\Notifier\Services\Implementations\Sms;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\TwilioException;
use Core\Notifier\Services\Contracts\SmsAdapter;

class TwilioSms implements SmsAdapter
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
    private $provider = 'Twilio';

    /**
     *
     * @var mixed
     */
    private $data;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($data = null)
    {
        $sid = env('TWILIO_SID'); // Your Account SID from www.twilio.com/console
        $token = env('TWILIO_AUTH_TOKEN'); // Your Auth Token from www.twilio.com/console

        if (!$this->client) {
            $this->client = new Client($sid, $token);
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

        try {
            if (empty($data['phone_number'])) {
                Log::info("[{$this->provider}][Send] => Phone number is empty.");
                return null;
            }

            if (!is_array($data['phone_number'])) {
                $data['phone_number'] = [$data['phone_number']];
            }

            $messages = [];

            // Send message to specified phones
            foreach ($data['phone_number'] as $phoneNumber) {
                $message = $this->client->messages->create(
                    $phoneNumber, //  'To' a valid phone number
                    [
                        'from' => $data['from'] ?? env('TWILIO_FROM'), // 'From' a valid Twilio number
                        'body' => $subject ?? $data['message'] ?? '',
                    ]
                );

                $messages[] = $message->toArray();

                Log::info("[{$this->provider}][{$phoneNumber}][Result] => ".json_encode_pretty($message->toArray()));
                Log::info("[{$this->provider}][{$phoneNumber}][Send] => success");
            }

            Log::info("[{$this->provider}][Result] => ".json_encode_pretty($messages));
            Log::info("[{$this->provider}][Send] => All messages sent.");

            return $messages;
        } catch (TwilioException $e) {
            Log::error("[{$this->provider}][Send][Error] => ".$e->getMessage());
        }

        return null;
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
        // Read TwiML at this URL when a call connects (hold music)
        try {
            $call = $this->client->calls->create(
                $to, // Call this number
                $from, // From a valid Twilio number
                $options
                // [
                //     'url' => 'https://twimlets.com/holdmusic?Bucket=com.twilio.music.ambient'
                // ]
            );
            return true;
        } catch (TwilioException $e) {
            Log::error("[{$this->provider}][Send][Error] => ".$e->getMessage());
        }
        return false;
    }
}
