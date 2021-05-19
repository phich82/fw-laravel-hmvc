<?php

namespace Core\Notifier\Services\Implementations;

use Core\Notifier\Services\Contracts\SmsAdapter;
use Core\Notifier\Services\Implementations\Sms\TwilioSms;

class Sms implements SmsAdapter
{
    private $client = null;

    /**
     * __construct
     *
     * @param  mixed $client
     * @return void
     */
    public function __construct($client = null)
    {
        $this->client = $client ?: new TwilioSms;
    }

    /**
     * __call
     *
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        if (in_array($method, ['send', 'makeCall'])) {
            return $this->{$method}(...$arguments);
        }

        $method = ucfirst($method);
        $namespace = self::_resolveNameSpace($arguments);
        $classSms = "{$namespace}\\{$method}Sms"; // TwilioSms, NexmoSms,...

        return new $classSms(...$arguments);
    }

    /**
     * __callStatic
     *
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        $method = ucfirst($method);
        $namespace = self::_resolveNameSpace($arguments);
        $classSms = "{$namespace}\\{$method}Sms"; // TwilioSms, NexmoSms,...

        return new $classSms(...$arguments);
    }

    /**
     * @implement
     *
     * Send message to mobile devices (sms)
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|bool
     */
    public function send($subject, $template, $data)
    {
        return $this->client->send($subject, $template, $data);
    }

    /**
     * Make a Call
     *
     * @return void
     */
    public function makeCall($to, $from, array $options = [])
    {
        return $this->client->makeCall($to, $from, $options);
    }
    
    /**
     * Resolve namespace of sms concrete classes
     *
     * @param  array $arguments
     * @return string
     */
    private static function _resolveNameSpace(&$arguments)
    {
        $namespace = '\Core\Notifier\Services\Implementations\Sms';
        if (!empty($arguments)) {
            $ns = $arguments[0];
            if (is_string($ns)) {
                $namespace = array_shift($arguments);
            }
        }
        return $namespace;
    }
}
