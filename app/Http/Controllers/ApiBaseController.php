<?php

namespace App\Http\Controllers;

use Core\Http\Traits\ApiResponse;
use Core\Http\Services\Contracts\HttpContract;

class ApiBaseController extends Controller
{
    use ApiResponse;

    protected $http;

    /**
     * __construct
     *
     * @param  \Core\Http\Services\Contracts\HttpContract $http
     * @return void
     */
    public function __construct(HttpContract $http)
    {
        $this->http = $http;
    }
}
