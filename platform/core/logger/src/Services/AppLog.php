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
    private const DATETIME_FORMAT = 'Y-m-d';
    private const FILENAME_POSTFIX_DELIMITER = '_';

    protected $channel = self::WEB_CHANNEL;
    protected $path = self::DEFAULT_LOG_FILE;
    /**
     * Version of api
     *
     * @var string
     */
    protected $version = '';

    /**
     * All declared channels MUST be mapped here
     *
     * @var array
     */
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
        $this->_setLogFilePathDynamically();
        Log::channel($this->channel)->{$method}(...$arguments);
        // Reset
        $this->version = '';
        $this->channel = self::WEB_CHANNEL;
        $this->path = $this->_webLogFilePathDefault();
    }

    /**
     * Set 'web' channel
     *
     * @param  string $version
     * @param  string $path
     * @return \AppLog
     */
    public function web($version = '', $path = null)
    {
        $this->version = $version;
        return $this->_resolve(self::WEB_CHANNEL, $path);
    }

    /**
     * Set 'api' channel
     *
     * @param  string $version
     * @param  string $path
     * @return \AppLog
     */
    public function api($version = '', $path = null)
    {
        $this->version = $version;
        return $this->_resolve(self::API_CHANNEL, $path);
    }

    /**
     * Set 'push' channel
     *
     * @param  string $version
     * @param  string $path
     * @return \AppLog
     */
    public function push($version = '', $path = null)
    {
        $this->version = $version;
        return $this->_resolve(self::PUSH_CHANNEL, $path);
    }

    /**
     * Set 'webhook' channel
     *
     * @param  string $version
     * @param  string $path
     * @return \AppLog
     */
    public function webhook($version = '', $path = null)
    {
        $this->version = $version;
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
    private function _setLogFilePathDynamically()
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
            // Get channel in the given file path
            $filenameCurrent = current(array_slice(explode('/', $this->path), -1));
            $filename = strtolower(explode(self::FILENAME_POSTFIX_DELIMITER, $filenameCurrent)[0]);
            $channel = strtolower($this->mappingFileNameToChannel[$filename]);
            // Set new path of the log file (dynnamically)
            $methodClass = "_{$channel}LogFilePathDefault";
            if (method_exists($this, $methodClass)) {
                $this->path = $this->{$methodClass}();
            }
        }
        // Set new path of the log file dynamically
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
        $postfix = $this->_postfix(date(self::DATETIME_FORMAT));
        $prefixMethod = strtolower($channel);
        $methodClass = "_{$prefixMethod}LogFilePathDefault";

        if ($path) {
            $this->path = $path;
        } elseif (method_exists($this, $methodClass)) {
            $this->path = $this->{$methodClass}($postfix);
        }

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
            return self::FILENAME_POSTFIX_DELIMITER.($postfix ?: date(self::DATETIME_FORMAT));
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
        $filename = $this->_getFileNameMapping(self::WEB_CHANNEL);
        $version = $this->version ? "/{$this->version}" : '';

        return "logs/web{$version}/{$filename}{$postfix}.log";
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
        $filename = $this->_getFileNameMapping(self::API_CHANNEL);
        $version = $this->version ? "/{$this->version}" : '';

        return "logs/api{$version}/{$filename}{$postfix}.log";
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
        $filename = $this->_getFileNameMapping(self::PUSH_CHANNEL);
        $version = $this->version ? "/{$this->version}" : '';

        return "logs/push{$version}/{$filename}{$postfix}.log";
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
        $filename = $this->_getFileNameMapping(self::WEBHOOK_CHANNEL);
        $version = $this->version ? "/{$this->version}" : '';

        return "logs/webhook{$version}/{$filename}{$postfix}.log";
    }

    /**
     * Get filename of the log file by channel
     *
     * @param  string $channel
     * @return null|string
     */
    private function _getFileNameMapping($channel)
    {
        foreach ($this->mappingFileNameToChannel as $filename => $channelMapping) {
            if ($channel == $channelMapping) {
                return $filename;
            }
        }
        return null;
    }
}
