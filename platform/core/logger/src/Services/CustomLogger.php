<?php

namespace Core\Logger\Services;

use Monolog\Logger;
use Core\Logger\Services\LoggerHandler;

class CustomLogger
{
    /**
     * __invoke
     * Create a custom Monolog instance
     *
     * @param  array $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config =[])
    {
        $logger = new Logger($this->_channel());
        $logger->pushHandler(new LoggerHandler());

        return $logger;
    }

    /**
     * Get channel name of log
     *
     * @return string
     */
    private function _channel()
    {
        return env('LOG_CHANNEL', 'custom');
    }
}
