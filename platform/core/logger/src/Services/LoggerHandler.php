<?php

namespace Core\Logger\Services;

use Core\Logger\Services\Facades\AppLog;
use Monolog\Logger;
use Illuminate\Support\Str;
use Monolog\Handler\AbstractProcessingHandler;

class LoggerHandler extends AbstractProcessingHandler
{
    /**
     * __construct
     *
     * @param  string $level
     * @return void
     */
    public function __construct($level = Logger::DEBUG)
    {
        parent::__construct($level);
    }

    /**
     * Write log to file
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record): void
    {
        $method = Str::lower($record['level_name']);
        $context = $record['context'] ?? [];
        $message = $record['message'] ?? '';

        if (isApi()) {
            AppLog::api()->{$method}($message, $context);
        } else {
            AppLog::web()->{$method}($message, $context);
        }
    }
}
