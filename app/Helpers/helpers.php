<?php

if (!function_exists('isApi')) {
    /**
     * Check it is api route
     *
     * @return bool
     */
    function isApi($version = '') {
        $version = $version ? "/{$version}" : '';
        return request()->is("api{$version}*");
    }
}
