<?php

namespace Api\V2\Http\Controllers;

use App\Http\Controllers\ApiBaseController;
use Core\Http\Services\Facades\Http;
use Core\Http\Services\Contracts\HttpContract;

class ApiController extends ApiBaseController
{
    /**
     * __construct
     *
     * @param  \Core\Http\Services\Contracts\HttpContract $http
     * @return void
     */
    public function __construct(HttpContract $http)
    {
        parent::__construct($http);
    }

    public function demo()
    {
        return response()->json([
            'message' => 'V2 Test Data'
        ], 200);
    }

    public function json()
    {
        $params = request()->all();
        $type = $params['type'] ?? 'users';
        // (new Http())->request('get');
        $response = Http::get("https://jsonplaceholder.typicode.com/{$type}", [], true);
        return response()->json($response, 200);
    }

    public function getUsers()
    {
        $response = $this->http->get('users');
        if ($response->success) {
            return $this->responseSuccess($response->data, $response->message, $response->code);
        }
        return $this->responseError($response->message, $response->code);
    }

    public function getPosts()
    {
        $response = $this->http->get('posts');
        return response()->json($response, 200);
    }

}
