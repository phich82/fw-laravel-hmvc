<?php

namespace App\Services\Implementations;

use App\Services\Contracts\CustomFormatter;

class ApiChannelCustomerFormatter extends CustomFormatter
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
        return "[%datetime%][API] %channel%.%level_name%: %message% %context% %extra%\n";
    }
}
