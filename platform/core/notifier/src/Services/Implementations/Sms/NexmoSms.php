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

        $success = [];
        $failed  = [];

        foreach ($data['phone_number'] as $phoneNumber) {
            try {
                $text = new \Vonage\SMS\Message\SMS(
                    $phoneNumber,
                    $data['from'] ?? env('NEXMO_FROM'),
                    $subject ?? $data['message'] ?? ''
                );

                $response = $this->client->sms()->send($text);

                $message = $response->current();

                Log::info("[{$this->provider}][{$phoneNumber}][Send][Result] => ".json_encode_pretty((array) $message));

                if ($message->getStatus() == 0) {
                    $success[] = $phoneNumber;
                    Log::info("[{$this->provider}][{$phoneNumber}][Send] => success");
                } else {
                    $failed[] = $phoneNumber;
                    Log::info("[{$this->provider}][{$phoneNumber}][Send] => failed");
                }
            } catch (Exception $e) {
                Log::error(__CLASS__.':'.__FUNCTION__."[{$phoneNumber}][Error] => {$e->getMessage()}");
                $failed[] = $phoneNumber;
            }
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
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
