<?php

namespace App\Services\Contracts;

use Monolog\Formatter\LineFormatter;

abstract class CustomFormatter
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter(new LineFormatter(
                // "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
                $this->getLogFormat(),
                null,
                true,
                true,
            ));
        }
    }

    /**
     * Get string format for each line
     *
     * @return string
     */
    abstract protected function getLogFormat();
}
