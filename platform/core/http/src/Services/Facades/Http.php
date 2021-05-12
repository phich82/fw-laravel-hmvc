<?php

namespace Core\Http\Services\Facades;

use Core\Http\Services\Contracts\HttpContract;
use Illuminate\Support\Facades\Facade;

class Http extends Facade
{
    /**
     * @override
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return HttpContract::class;
    }
}
