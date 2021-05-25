<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SlackAdapter;
use Exception;

class Slack extends BaseNotifier implements SlackAdapter
{
    /**
     * @var string
     */
    protected $provider = 'Slack';

    /**
     * @var \Maknz\Slack\Client
     */
    private $client;

    /**
     * __construct
     *
     * @param  array $data
     * @param  array $config
     * @return void
     */
    public function __construct(array $data = [], array $config = [])
    {
        parent::__construct($data);

        // Default setting
        if (empty($config)) {
            $config = [
                'username' => env('SLACK_USERNAME', 'System'),
                'channel' => env('SLACK_CHANNEL', 'general'),
                'link_names' => env('SLACK_LINK_NAMES', true),
            ];
        }
        $this->client = new \Maknz\Slack\Client(env('SLACK_WEBHOOK'), $config);
    }

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

        $message = $data['message'] ?? $template;
        $listTo = $data['to'] ?? [];

        if (!is_array($listTo)) {
            $listTo = [$listTo];
        }

        foreach ($listTo as $to) {
            try {
                $client = $this->client;
                if (isset($data['channel'])) {
                    $client->to($data['channel']);
                }
                if (isset($data['icon'])) {
                    $client->withIcon($data['icon']);
                }
                if (isset($data['attachment'])) {
                    $client->attach($data['attachment']);
                }
                $client->to($to)->send($message);
                $success[] = $to;
                Log::info("[{$this->provider}][{$to}][Send] => success");
            } catch (Exception $e) {
                $failed[] = $to;
                Log::error(__CLASS__.':'.__FUNCTION__."[{$to}][Error] => {$e->getMessage()}");
            }
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
    }
}
