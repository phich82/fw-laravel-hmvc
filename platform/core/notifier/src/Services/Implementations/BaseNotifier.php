<?php

namespace Core\Notifier\Services\Implementations;

class BaseNotifier
{
/**
     * @var array
     */
    protected $data = [];

    /**
     * Provider name
     *
     * @var string
     */
    protected $provider = '';

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
}
