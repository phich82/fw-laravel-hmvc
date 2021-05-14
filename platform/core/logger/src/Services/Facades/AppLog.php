<?php

namespace Core\Logger\Services\Facades;

use Core\Logger\Services\AppLog as ServicesAppLog;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void emergency(string $message, array $context = [])
 * @method static void alert(string $message, array $context = [])
 * @method static void critical(string $message, array $context = [])
 * @method static void error(string $message, array $context = [])
 * @method static void warning(string $message, array $context = [])
 * @method static void notice(string $message, array $context = [])
 * @method static void info(string $message, array $context = [])
 * @method static void debug(string $message, array $context = [])
 * @method static void log($level, string $message, array $context = [])
 * @method static mixed channel(string $chnnel = null)
 * @method static \Psr\Log\LoggerInterface stack(array $chnnels, string $channel = null)
 * @method static \Core\Logger\Services\AppLog web(string $path = null)
 * @method static \Core\Logger\Services\AppLog api(string $path = null)
 * @method static \Core\Logger\Services\AppLog push(string $path = null)
 * @method static \Core\Logger\Services\AppLog webhook(string $path = null)
 *
 * @see \Illuminate\Log\Logger
 */
class AppLog extends Facade
{
    /**
     * @override
     *
     * Get the registered name of the component.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return ServicesAppLog::class;
    }
}
