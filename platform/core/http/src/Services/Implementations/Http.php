<?php

namespace Core\Http\Services\Implementations;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ConnectException;
use Core\Http\Services\Contracts\HttpContract;
use Core\Http\Traits\ApiResponse;

/**
 * @method static mixed get($path, $options = [], $async = false)
 * @method static mixed post($path, $options = [], $async = false)
 * @method static mixed put($path, $options = [], $async = false)
 * @method static mixed path($path, $options = [], $async = false)
 * @method static mixed delete($path, $options = [], $async = false)
 * @method static mixed head($path, $options = [], $async = false)
 * @method static mixed options($path, $options = [], $async = false)
 * @method mixed get($path, $options = [], $async = false)
 * @method mixed post($path, $options = [], $async = false)
 * @method mixed put($path, $options = [], $async = false)
 * @method mixed path($path, $options = [], $async = false)
 * @method mixed delete($path, $options = [], $async = false)
 * @method mixed head($path, $options = [], $async = false)
 * @method mixed options($path, $options = [], $async = false)
 * @method static mixed getAsync($path, $options = [], $async = false)
 * @method static mixed postAsync($path, $options = [], $async = false)
 * @method static mixed putAsync($path, $options = [], $async = false)
 * @method static mixed pathAsync($path, $options = [], $async = false)
 * @method static mixed deleteAsync($path, $options = [], $async = false)
 * @method static mixed headAsync($path, $options = [], $async = false)
 * @method static mixed optionsAsync($path, $options = [], $async = false)
 * @method mixed getAsync($path, $options = [], $async = false)
 * @method mixed postAsync($path, $options = [], $async = false)
 * @method mixed putAsync($path, $options = [], $async = false)
 * @method mixed pathAsync($path, $options = [], $async = false)
 * @method mixed deleteAsync($path, $options = [], $async = false)
 * @method mixed headAsync($path, $options = [], $async = false)
 * @method mixed optionsAsync($path, $options = [], $async = false)
 *
 * @see https://docs.guzzlephp.org/en/stable/
 */
class Http implements HttpContract
{
    use ApiResponse;

    protected $http;
    protected $config = [];
    protected $params = [];
    protected $paramsQuery = [];
    protected $commonKey = 'common';
    protected $bodyTypeKey = 'body_type';
    protected $bodyType = 'json'; // json|body|form_params|multipart
    protected $queryKey = 'query';
    protected $version = ''; // api version
    protected $prefix = ''; // api prefix

    /**
     * __construct
     *
     * @param  array $config
     * @return void
     */
    public function __construct(array $config = [])
    {
        $this->_resolveConfig($config);

        $this->http = new Client($this->config);
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
        $requestMethod = str_replace('async', '', strtolower($method));
        $request = strlen($requestMethod) === strlen($method) ? 'request' : 'requestAsync';

        return $this->{$request}(strtoupper($requestMethod), ...$arguments);
    }

    /**
     * Request http
     *
     * @param  string $method
     * @param  string $path
     * @param  array $params
     * @param  mixed $async
     * @return mixed
     */
    public function request($method, $path, array $params = [], $async = false)
    {
        try {
            $path = $this->_resolvePath($path, $async);
            $options = $this->_resolveOptions($method, $params, $async);
            $async = $this->_isAsync($async);


            if ($async) {
                return $this->_resolveResponseAsync($this->http->requestAsync($method, $path, $options));
            }
            return $this->_resolveResponse($this->http->request($method, $path, $options));
        } catch (Exception $e) {
            return $this->_resolveException($e);
        }
    }

    /**
     * Request http asynchronously
     *
     * @param  string $method
     * @param  string $path
     * @param  mixed $params
     * @param  bool|array $async
     * @return \GuzzleHttp\Promise\Promise
     */
    public function requestAsync($method, $path, $params = [], $async = false)
    {
        if (is_array($async)) {
            $async['async'] = true;
        } else {
            $async = true;
        }
        return $this->request($method, $path, $params, $async);
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
            return $this->responseSuccess(json_decode($body->getContents()), $reason, $code)->getData();
        }
        // Failed
        return $this->responseError($reason, $code)->getData();
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

        return $this->responseError($message, $code)->getData();
    }

    /**
     * _Default config
     *
     * @return array
     *
     * @see https://docs.guzzlephp.org/en/stable/request-options.html
     */
    private function _defaultConfig()
    {
        return [
            'base_uri' => '',
            'headers' => [
                'Cache-Control' => 'no-cache',
                'Content-Type' => 'application/json',
                'Accept' => '*/*'
            ],
            'timeout' => 0, // wait indefinitely (seconds)
            'http_errors' => true,
            'debug' => false,
            'verify' => false,
        ];
    }

    /**
     * Resolve config
     *
     * @param  mixed $config
     * @return void
     */
    private function _resolveConfig($config = [])
    {
        $default = $this->_defaultConfig();
        if (array_key_exists('base_uri', $config)) {
            $default['base_uri'] = rtrim($config['base_uri'], '/');
            unset($config['base_uri']);
        }
        if (array_key_exists('headers', $config)) {
            $default['headers'] = array_merge($default['headers'], $config['headers']);
            unset($config['headers']);
        }
        // Set 'common' parameter if found
        if (array_key_exists($this->commonKey, $config)) {
            if (array_key_exists($this->queryKey, $config[$this->commonKey])) {
                $this->paramsQuery = $config[$this->commonKey][$this->queryKey];
            }
            if (array_key_exists('params', $config[$this->commonKey])) {
                $this->params = $config[$this->commonKey]['params'];
            }
            if (array_key_exists('prefix', $config[$this->commonKey])) {
                $this->prefix = $config[$this->commonKey]['prefix'];
            }
            if (array_key_exists('version', $config[$this->commonKey])) {
                $this->version = $config[$this->commonKey]['version'];
            }
            unset($config[$this->commonKey]);
        }
        $this->config = array_merge($default, $config);
    }

    /**
     * Resolve route path
     *
     * @param  string $path
     * @param  bool|array $async
     * @return string
     */
    private function _resolvePath($path, $async)
    {
        // If the path is full url (start with http(s))
        if (preg_match('#^http(s)?#i', $path)) {
            return $path;
        }

        $version = $this->version;
        $prefix = $this->prefix;

        if (is_array($async) && array_key_exists('force_version', $async)) {
            $version = $async['force_version'];
        }

        // Api prefix
        if ($prefix) {
            $prefix = '/'.trim($prefix, '/').'/';
        }

        // Api version
        if ($version) {
            $version = '/'.trim($version, '/').'/';
            $prefix = trim($prefix, '/').$version;
        }

        return $prefix.ltrim($path, '/');
    }

    /**
     * Resolve options of request
     *
     * @param  string $method
     * @param  array $params
     * @param  array $async
     * @return array
     */
    private function _resolveOptions($method, $params, $async)
    {
        // If request method is GET
        if (in_array(strtoupper($method), ['GET', 'HEAD', 'OPTIONS', 'CONNECT', 'TRACE'])) {
            return [
                $this->queryKey => array_merge($this->paramsQuery, $params)
            ];
        }

        if (is_bool($async)) {
            $async['async'] = $async;
        }

        $bodyType = $this->bodyType;

        if (isset($async[$this->bodyTypeKey]) && in_array($async[$this->bodyTypeKey], $this->_supportedBodyTypes())) {
            $bodyType = $async[$this->bodyTypeKey];
        }

        return [
            $bodyType => array_merge($this->params, $params ?: []),
            $this->queryKey => array_merge($this->paramsQuery, $async[$this->queryKey] ?? []),
        ];

        // $bodyTypes = array_values(array_intersect($this->_supportedBodyTypes(), array_keys($params)));
        // if (!empty($bodyTypes)) {
        //     if (array_key_exists($this->bodyType, $params)) {
        //         $params[$this->bodyType] = array_merge($this->params, $params[$this->bodyType]);
        //     }
        //     if (!empty($this->paramsQuery)) {
        //         $params[$this->queryKey] = array_merge($this->paramsQuery, $params[$this->queryKey] ?? []);
        //     }
        //     return $params;
        // }

        // // If request method is GET
        // if (in_array(strtoupper($method), ['GET'])) {
        //     return [
        //         $this->queryKey => array_merge($this->paramsQuery, $params)
        //     ];
        // }

        // // Others
        // $options = [$this->bodyType => array_merge($this->params, $params)];
        // if ($this->bodyType != $this->queryKey && !empty($this->paramsQuery)) {
        //     $options[$this->queryKey] = $this->paramsQuery;
        // }
        // return $options;
    }

    /**
     * Body types are supported
     *
     * @return array
     */
    private function _supportedBodyTypes()
    {
        return ['json', 'body', 'query', 'form_params', 'multipart'];
    }

    /**
     * Check request is asynchronously
     *
     * @param  bool|array $async
     * @return bool
     */
    private function _isAsync($async)
    {
        if (is_bool($async)) {
            return $async;
        }
        if (is_array($async) && array_key_exists('async', $async)) {
            return $async['async'];
        }
        return false;
    }
}
