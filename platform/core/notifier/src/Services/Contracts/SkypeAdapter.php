<?php

namespace Core\Notifier\Services\Contracts;

interface SkypeAdapter
{
    /**
     * Send message
     *
     * @param  string $subject
     * @param  string $template
     * @param  mixed $data
     * @return bool|void
     */
    public function send($subject, $template, $data);
}
