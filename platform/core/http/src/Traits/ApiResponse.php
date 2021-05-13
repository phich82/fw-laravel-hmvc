<?php

namespace Core\Http\Traits;

use Core\Http\Constants\Constant;
use Illuminate\Http\Response;

trait ApiResponse
{
    /**
     * Response successful
     *
     * @param  mixed $data
     * @param  mixed $message
     * @param  int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseSuccess($data = null, $message = '', $code = Response::HTTP_OK)
    {
        return $this->tojson([
            Constant::API_RESPONSE_SUCCESS => true,
            Constant::API_RESPONSE_CODE => $code,
            Constant::API_RESPONSE_MESSAGE => $message,
            Constant::API_RESPONSE_DATA => $data,
        ]);
    }

    /**
     * Response failed
     *
     * @param  mixed $message
     * @param  int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function responseError($message = '', $code = Response::HTTP_BAD_REQUEST)
    {
        return $this->tojson([
            Constant::API_RESPONSE_SUCCESS => false,
            Constant::API_RESPONSE_CODE => $code,
            Constant::API_RESPONSE_MESSAGE => $message,
            Constant::API_RESPONSE_DATA => null,
        ]);
    }

    /**
     * Create a new JSON response instance.
     *
     * @param  string|array|object $result
     * @return \Illuminate\Http\JsonResponse
     */
    private function tojson($result)
    {
        return response()->json($result);
    }
}
