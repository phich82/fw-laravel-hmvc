<?php

namespace Core\Notifier\Services\Implementations;

use Illuminate\Support\Facades\Log;
use Core\Notifier\Services\Contracts\SkypeAdapter;
use Core\Notifier\Services\Implementations\Skype\SkypePHP;
use Exception;

class Skype extends BaseNotifier implements SkypeAdapter
{
    /**
     * Provider name
     *
     * @var string
     */
    protected $provider = 'Skype';

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
            $skype->login(env('SKYPE_USERNAME', $data['username']), env('SKYPE_PASSWORD', $data['password'])) or die('Login failed');

            $listTo = $data['to'] ?? [];
            foreach ($listTo as $skypeId) {
                $currentSkypeId = $skypeId;
                $skype->sendMessage($skypeId, $message);
                $success[] = $currentSkypeId;
                Log::info("[{$this->provider}][{$skypeId}][Send] => success");
            }
        } catch (Exception $e) {
            $failed[] = $currentSkypeId;
            Log::error("[{$this->provider}][".__CLASS__.':'.__FUNCTION__."][{$currentSkypeId}][Error] => {$e->getMessage()}");
        }

        if (!empty($failed)) {
            Log::info("[{$this->provider}][Send] => Total unsent => ".count($failed));
        }
        Log::info("[{$this->provider}][Send] => Total sent => ".count($success));

        return count($success);
    }
}
