<?php

namespace Api\V2\Http\Controllers;

use App\Http\Controllers\Controller;
use Core\Http\Services\Facades\Http;

class ApiController extends Controller
{
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

}
