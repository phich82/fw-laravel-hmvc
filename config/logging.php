<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Logger;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 14,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        'web' => [
            'driver' => 'single',
            'path' => storage_path('logs/web/admin.log'),
            'level' => 'debug',
            'permission' => 0755,
        ],

        'api' => [
            'driver' => 'single',
            // Customize format of each log line
            'tap' => [App\Services\Implementations\ApiChannelCustomerFormatter::class],
            'path' => storage_path('logs/api/api.log'),
            'level' => 'debug',
            'permission' => 0755,
        ],

        'push' => [
            'driver' => 'single',
            // Customize format of each log line
            'tap' => [App\Services\Implementations\PushChannelCustomerFormatter::class],
            'path' => storage_path('logs/push/push.log'),
            'level' => 'debug',
            'permission' => 0755,
        ],

        'webhook' => [
            'driver' => 'single',
            // Customize format of each log line
            'tap' => [App\Services\Implementations\WebhookChannelCustomerFormatter::class],
            'path' => storage_path('logs/webhook/webhook.log'),
            'level' => 'debug',
            'permission' => 0755,
        ],

        'custom' => [
            'driver' => 'custom',
            'via' => \Core\Logger\Services\CustomLogger::class,
            // extra config for send log message to specified channels (servers: email, slack, skype,...)
            'extra' => [
                'notifier' => \Core\Notifier\Services\Contracts\LogNotifierContract::class,
                'method' => 'send',
                'levels' => [Logger::CRITICAL, Logger::EMERGENCY, Logger::ERROR],
                'queue' => '',
                // Resolve arguments of 'send' method of notifier class
                'callable' => function (array $error) {
                    $subject = "[{$error['level_name']}]Error Log System";
                    $template = 'error';
                    $data = $error + [
                        'from' => '',
                        'from_name' => '',
                        'to' => '',
                        'to_name' => '',
                        'cc' => '',
                        'bcc' => '',
                    ];
                    return [$subject, $template, $data];
                },
            ],
        ]

    ],

];
