<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class Notifier
{
    /**
    *  __Construct method
    */
    public function __construct()
    {
        //
    }

    public function send($message, $context)
    {
        Log::info('Message sent: ', ['message' => $message, 'context' => $context]);
    }
}
