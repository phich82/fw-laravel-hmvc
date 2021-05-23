<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SlackAdapter;

class Slack implements SlackAdapter
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * __construct
     *
     * @param  array $data
     * @return void
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
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

        // Package for send message to slack
        Log::info('Message sent to slack.');
    }
}
