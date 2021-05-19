<?php

if (!function_exists('isApi')) {
    /**
     * Check it is api route
     *
     * @param string $version
     * @return bool
     */
    function isApi($version = '') {
        $version = $version ? "/{$version}" : '';
        return request()->is("api{$version}*");
    }
}

if (!function_exists('isWebhook')) {
    /**
     * Check it is webhook route
     *
     * @param string $version
     * @return bool
     */
    function isWebhook($version = '') {
        $version = $version ? "/{$version}" : '';
        return request()->is("webhook{$version}*");
    }
}

if (!function_exists('isPush')) {
    /**
     * Check it is push notification route
     *
     * @param string $version
     * @return bool
     */
    function isPush($version = '') {
        $version = $version ? "/{$version}" : '';
        return request()->is("push{$version}*");
    }
}

if (!function_exists('getApiVersion')) {
    /**
     * Get version of api route
     *
     * @return string
     */
    function getApiVersion() {
        preg_match('#^api\/(v\d+)\/#i', request()->path(), $matches);
        if (count($matches) === 2) {
            return $matches[1];
        }
        return '';
    }
}

if (!function_exists('getWebhookVersion')) {
    /**
     * Get version of api route
     *
     * @return string
     */
    function getWebhookVersion() {
        preg_match('#^webhook\/(v\d+)\/#i', request()->path(), $matches);
        if (count($matches) === 2) {
            return $matches[1];
        }
        return '';
    }
}

if (!function_exists('getPushVersion')) {
    /**
     * Get version of api route
     *
     * @return string
     */
    function getPushVersion() {
        preg_match('#^push\/(v\d+)\/#i', request()->path(), $matches);
        if (count($matches) === 2) {
            return $matches[1];
        }
        return '';
    }
}

if (!function_exists('json_encode_pretty')) {
    /**
     * Get version of api route
     *
     * @param mixed $data
     * @return string
     */
    function json_encode_pretty($data) {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
