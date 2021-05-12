<?php

namespace Core\Http\Services\Implementations;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use Core\Http\Services\Contracts\HttpContract;

class Http implements HttpContract
{
    private $http;

    /**
     * __construct
     *
     * @param  mixed $config
     * @return void
     */
    public function __construct($config = [])
    {
       $this->http = new Client(array_merge([
           'base_uri' => '',
           'verify' => false,
           'http_errors' => false,
       ], $config));
    }

    /**
     * __call
     *
     * @param  string $method
     * @param  array $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        $requestMethod = str_replace('Async', '', $method);
        $request = $requestMethod != $method ? 'requestAsync' : 'request';
        $async = $requestMethod != $method;

        return $this->request(strtoupper($requestMethod), ...$arguments);
    }

    /**
     * Request http
     *
     * @param  string $method
     * @param  string $path
     * @param  mixed $params
     * @param  mixed $async
     * @return void
     */
    public function request($method, $path, $params = [], $async = false)
    {
        try {
            $options = $params;
            $async = is_array($async) ? $async['async'] ?? false : $async;
            if ($async) {
                return $this->_resolveResponseAsync($this->http->requestAsync($method, $path, $options));
            }
            dd($method, $path, $options, $async);
            return $this->_resolveResponse($this->http->request($method, $path, $options));
        } catch (Exception $e) {
            return $this->_resolveException($e);
        }
    }

    /**
     * Resolve response
     *
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @return mixed
     */
    private function _resolveResponse(ResponseInterface $response)
    {
        $code = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $body = $response->getBody();

        // Success
        if ($code < 400) {
            return response()->json([
                'success' => true,
                'code' => $code,
                'message' => $reason,
                'data' => json_decode($body->getContents())
            ])->getData();
        }
        // Failed
        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $reason,
            'data' => null
        ])->getData();
    }

    /**
     * Resolve promise response
     *
     * @param  \GuzzleHttp\Promise\Promise $responsePromise
     * @return mixed
     */
    private function _resolveResponseAsync(Promise $responsePromise)
    {
        // return $responsePromise->then(
        //     function (ResponseInterface $response) {
        //         return $this->_resolveResponse($response);
        //     },
        //     function (Exception $e) {
        //         return $this->_resolveException($e);
        //     }
        // )->wait();
        // Use Arrow Function: fn() =>
        return $responsePromise->then(
            fn(ResponseInterface $response) => $this->_resolveResponse($response),
            fn(Exception $e) => $this->_resolveException($e)
        )->wait();
    }

    /**
     * Resolve http exception
     *
     * @param  mixed $e
     * @return mixed
     */
    private function _resolveException($e)
    {
        $request = $e->getRequest();
        $message = null;
        $code = 0;

        if ($e instanceof ConnectException) {
            $context = $e->getHandlerContext();
            $message = $context['error'];
            $code = $context['http_code'] ?? 0;
        } else {
            $message = $e->getMessage() ?? null;
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $code = $response->getStatusCode() ?? 0;
                $message = $response->getReasonPhrase();
            }
        }

        return response()->json([
            'success' => false,
            'code' => $code,
            'message' => $message,
            'data' => null,
        ])->getData();
    }
}
