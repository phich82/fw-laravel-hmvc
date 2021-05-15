<?php

namespace Core\Http\Services\Contracts;


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
interface HttpContract
{
    /**
     * Request http
     *
     * @param string $method
     * @param string $path
     * @param array $options
     * @param bool|array $async
     * @return mixed
     */
    public function request($method, $path, array $options = [], $async = false);
}
