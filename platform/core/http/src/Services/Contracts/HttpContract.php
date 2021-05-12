<?php

namespace Core\Http\Services\Contracts;

interface HttpContract
{
    /**
     * Request http
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @param bool $async
     * @return mixed
     */
    public function request($method, $path, $options = [], $async = false);
}
