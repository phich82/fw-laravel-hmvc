<?php

namespace Core\Logger\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class AppLog
{
    private const WEB_CHANNEL = 'web';
    private const API_CHANNEL = 'api';
    private const PUSH_CHANNEL = 'push';
    private const WEBHOOK_CHANNEL = 'webhook';

    private const DEFAULT_LOG_FILE = 'logs/web/admin.log';
    private const USE_POSTFIX_DYNAMICALLY = true;

    protected $channel = self::WEB_CHANNEL;
    protected $path = self::DEFAULT_LOG_FILE;

    private $mappingFileNameToChannel = [
        'admin'   => self::WEB_CHANNEL,
        'api'     => self::API_CHANNEL,
        'push'    => self::PUSH_CHANNEL,
        'webhook' => self::WEBHOOK_CHANNEL,
    ];

    /**
     * __call
     *
     * @param  string $method
     * @param  array $arguments
     * @return void
     */
    public function __call($method, $arguments)
    {
        $this->_validate();
        Log::channel($this->channel)->{$method}(...$arguments);
        $this->channel = self::WEB_CHANNEL;
        $this->path = $this->_webLogFilePathDefault();
    }

    /**
     * Set 'web' channel
     *
     * @param  string $path
     * @return \AppLog
     */
    public function web($path = null)
    {
        return $this->_resolve(self::WEB_CHANNEL, $path);
    }

    /**
     * Set 'api' channel
     *
     * @param  string $path
     * @return \AppLog
     */
    public function api($path = null)
    {
        return $this->_resolve(self::API_CHANNEL, $path);
    }

    /**
     * Set 'push' channel
     *
     * @param  string $path
     * @return \AppLog
     */
    public function push($path = null)
    {
        return $this->_resolve(self::PUSH_CHANNEL, $path);
    }

    /**
     * Set 'webhook' channel
     *
     * @param  string $path
     * @return \AppLog
     */
    public function webhook($path = null)
    {
        return $this->_resolve(self::WEBHOOK_CHANNEL, $path);
    }

    /**
     * Get current path of log file
     *
     * @return string
     */
    protected function getLogFilePath()
    {
        return storage_path($this->path);
    }

    /**
     * Check exists of log file and channel
     *
     * @return void
     * @throws \Exception
     */
    private function _validate()
    {
        // Verify channel
        $channelTarget = "logging.channels.{$this->channel}";
        if (!config($channelTarget)) {
            throw new Exception(
                "Log channel [{$this->channel}] not exist. Please define it in 'config/logging.php' file."
            );
        }
        $keyPath = "{$channelTarget}.path";
        $defaultPath = config($keyPath);
        // Set name of log file dynamically
        if (self::USE_POSTFIX_DYNAMICALLY) {
            $filenameCurrent = current(array_slice(explode('/', $this->path), -1));
            $prefixFileName = strtolower(explode('.', $filenameCurrent)[0]);
            $prefixFileName = strtolower(explode('_', $prefixFileName)[0]);
            $channel = strtolower($this->mappingFileNameToChannel[$prefixFileName]);

            $method = "_{$channel}LogFilePathDefault";
            $this->path = $this->{$method}();
        }
        // Verify the log file & change the default path of it
        $pathLogFile = $this->getLogFilePath();
        if (!file_exists($pathLogFile) || $defaultPath != $pathLogFile) {
            config()->set($keyPath, $pathLogFile);
        }
    }

    /**
     * Resolve parameters
     *
     * @param  string $channel
     * @param  string $path
     * @return \AppLog
     */
    private function _resolve($channel, $path = null)
    {
        $postfix = $this->_postfix(date('Y-m-d'));
        $prefixMethod = strtolower($channel);

        $this->path = $path ?: $this->{"_{$prefixMethod}LogFilePathDefault"}($postfix);
        $this->channel = $channel;

        return $this;
    }

    /**
     * Create a postfix string for appending to filename of the log file
     *
     * @param  string $postfix
     * @return string
     */
    private function _postfix($postfix = null)
    {
        if (self::USE_POSTFIX_DYNAMICALLY) {
            return '_'.($postfix ?: date('Y-m-d'));
        }
        return '';
    }

    /**
     * The default path of log file for 'web' channel
     *
     * @param  string $postfix
     * @return string
     */
    private function _webLogFilePathDefault($postfix = null)
    {
        $postfix = $postfix ?: $this->_postfix();
        $channel = strtolower(self::WEB_CHANNEL);

        return "logs/web/admin{$postfix}.log";
    }

    /**
     * The default path of log file for 'api' channel
     *
     * @param  string $postfix
     * @return string
     */
    private function _apiLogFilePathDefault($postfix = null)
    {
        $postfix = $postfix ?: $this->_postfix();

        return "logs/api/api{$postfix}.log";
    }

    /**
     * The default path of log file for 'push' channel
     *
     * @param  string $postfix
     * @return string
     */
    private function _pushLogFilePathDefault($postfix = null)
    {
        $postfix = $postfix ?: $this->_postfix();

        return "logs/web/push{$postfix}.log";
    }

    /**
     * The default path of log file for 'webhook' channel
     *
     * @param  string $postfix
     * @return string
     */
    private function _webhookLogFilePathDefault($postfix = null)
    {
        $postfix = $postfix ?: $this->_postfix();

        return "logs/web/webhook{$postfix}.log";
    }
}
