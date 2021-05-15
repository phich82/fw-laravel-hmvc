<?php

namespace App\Services\Implementations;

use App\Services\Contracts\CustomFormatter;

class PushChannelCustomerFormatter extends CustomFormatter
{
    /**
     * @implement
     *
     * Get string format for each log line
     *
     * @return string
     */
    public function getLogFormat()
    {
        return "[%datetime%][PUSH] %channel%.%level_name%: %message% %context% %extra%\n";
    }
}
