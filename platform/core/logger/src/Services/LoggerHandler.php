<?php

namespace Core\Logger\Services;

use Core\Logger\Services\Facades\AppLog;
use Monolog\Logger;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;

class LoggerHandler extends AbstractProcessingHandler
{
    protected $targetChannel;

    /**
     * __construct
     *
     * @param  string $level
     * @param  string $level
     * @return void
     */
    public function __construct($channel = null, $level = Logger::DEBUG)
    {
        parent::__construct($level);

        $this->targetChannel = $channel;
    }

    /**
     * Write log to file
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record): void
    {
        $level = $record['level'];
        $method = Str::lower($record['level_name']);
        $context = $record['context'] ?? [];
        $message = $record['message'] ?? '';

        $this->buildExtra($record);

        if (isApi()) {
            AppLog::api(getApiVersion())->{$method}($message, $context);
        } elseif (isWebhook()) {
            AppLog::webhook(getWebhookVersion())->{$method}($message, $context);
        } elseif (isPush()) {
            AppLog::push(getPushVersion())->{$method}($message, $context);
        } else {
            AppLog::web()->{$method}($message, $context);
        }

        // Send log to specified servers based on error levels
        $this->dispatchNotification($record);
    }

    /**
     * Send log message to specified channels (servers: email, slack, skype, pusher,...)
     *
     * @param  array $record
     * @return void
     */
    private function dispatchNotification(array $record)
    {
        // Send log to specified servers based on error levels
        if ($this->targetChannel && config("logging.channels.{$this->targetChannel}.extra", null)) {
            $extra = config("logging.channels.{$this->targetChannel}.extra");
            $levelsAllowed = $extra['levels'] ?? [];
            $notifier = $extra['notifier'] ?? null;
            $send = $extra['method'] ?? 'send';

            // If error level allowed
            if (
                in_array($record['level'], $levelsAllowed) &&
                !empty($extra['data']) &&
                is_string($notifier) &&
                is_callable($extra['callable'])
            ) {
                $arguments = $extra['callable']($record);
                // Send message
                app()->make($notifier)->{$send}(...$arguments);
            }
        }
    }

    /**
     * Build extra data
     *
     * @param  array $record
     * @return void
     */
    private function buildExtra(&$record)
    {
        if (!array_key_exists('extra', $record)) {
            $record['extra'] = [];
        }

        $record['extra']['ip'] = request()->ip();
        $record['extra']['host'] = request()->getHost();
    }
}
