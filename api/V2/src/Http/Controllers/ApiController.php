<?php

namespace Api\V2\Http\Controllers;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    public function demo()
    {
        return response()->json([
            'message' => 'V2 Test Data'
        ], 200);
    }

}
