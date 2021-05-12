<?php

namespace Api\V1\Http\Controllers;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function demo()
    {
        return response()->json([
            'message' => 'Test Data'
        ], 200);
    }

}
