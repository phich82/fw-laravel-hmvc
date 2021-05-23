<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SkypeAdapter;
use Core\Notifier\Services\Implementations\Skype\SkypePHP;
use Exception;

class Skype implements SkypeAdapter
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Provider name
     *
     * @var string
     */
    private $provider = 'Skype';

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
     * Send message to skype
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return void|int|bool
     */
    public function send($subject, $template, $data)
    {
        $message = $template;
        $pathCacheSkype = '';

        if (!empty($this->data)) {
            $data = $this->data;
            $message = $data['message'] ?? $template;
            $pathCacheSkype = $data['cache_path'] ?? '';
        }

        $skype = new SkypePHP($pathCacheSkype);

        $success = [];
        $failed  = [];
        $currentSkypeId = null;

        try {
            $skype->login($data['username'], $data['password']) or die('Login failed');

            $listTo = $data['to'] ?? [];
            foreach ($listTo as $skypeId) {
                $currentSkypeId = $skypeId;
                $skype->sendMessage($skypeId, $message);
                $success[] = $currentSkypeId;
                Log::info("[{$this->provider}][{$skypeId}][Send] => success");
            }
        } catch (Exception $e) {
            $failed[] = $currentSkypeId;
            Log::error(__CLASS__.':'.__FUNCTION__."[{$currentSkypeId}][Error] => {$e->getMessage()}");
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
    }
}
