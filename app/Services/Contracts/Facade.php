<?php

namespace App\Services\Contracts;

use ReflectionMethod;

abstract class Facade
{
    /**
     * Get target class name for creating facade
     *
     * @return string
     */
    abstract static function getAssessorFacade();

    /**
     * __callStatic
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     */
    public static function __callStatic($method, $args = [])
    {
        $class = static::getAssessorFacade();
        $MethodChecker = new ReflectionMethod($class, $method);
        if ($MethodChecker->isStatic()) {
            return $class::{$method}(...$args);
        }
        return (new $class)->{$method}(...$args);
    }
}
