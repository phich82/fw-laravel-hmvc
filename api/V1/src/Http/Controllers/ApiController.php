<?php

namespace Api\V1\Http\Controllers;

use App\Http\Controllers\ApiBaseController;
use Core\Http\Services\Contracts\HttpContract;
use Illuminate\Support\Facades\Log;

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
        Log::info('Test Log');
        return response()->json([
            'message' => 'Test Data'
        ], 200);
    }

    public function json()
    {
        dd('json test eeee');
    }

    public function getUsers()
    {
        $response = $this->http->get('users');
        if ($response->success) {
            return $this->responseSuccess($response->data, $response->message, $response->code);
        }
        return $this->responseError($response->message, $response->code);
    }

    public function testPost()
    {
        $response = $this->http->post('uesrs', ['name' => 'Jhp Phich', 'job' => 'Design Web']);
        dd($response);
    }

    public function testPut()
    {
        $response = $this->http->put('uesrs/2', ['name' => 'Jhp Phich', 'job' => 'Design Web']);
        dd($response);
    }

    public function testPatch()
    {
        $response = $this->http->put('uesrs/2', ['name' => 'Jhp Phich', 'job' => 'Design Web']);
        dd($response);
    }

    public function testDelete()
    {
        $response = $this->http->delete('uesrs/2');
        dd($response);
    }

}
