<?php

namespace MinasRouter\Router;

use MinasRouter\Router\RouteCollection;

abstract class Route extends RouteCollection
{
    protected static $projectUrl;

    public static $collection;

    protected static $separator = '@';

    /**
     * @param string $projectUrl
     * @param string $separator = null
     */
    public static function start(String $projectUrl, String $separator = null): void
    {
        self::$projectUrl = rtrim($projectUrl, '/');

        if($separator) {
            self::$separator = $separator;
        }

        self::$collection = new parent(
                self::$separator,
                self::$projectUrl
            );
    }

    /**
     * @param string $projectUrl
     */
    public static function get(String $uri, $callback)
    {
        return self::$collection->addRoute("GET", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function post(String $uri, $callback)
    {
        return self::$collection->addRoute("POST", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function put(String $uri, $callback)
    {
        return self::$collection->addRoute("PUT", $uri, $callback);
    }
    
    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function patch(String $uri, $callback)
    {
        return self::$collection->addRoute("PATCH", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function delete(String $uri, $callback)
    {
        return self::$collection->addRoute("DELETE", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function any(String $uri, $callback)
    {
        return self::$collection->addRoute("any", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     */
    public static function match(Array $uri, $callback)
    {
        return self::$collection->addRoute("match", $uri, $callback);
    }

    public static function __callStatic($method, $arguments)
    {
        return self::$collection->{$method}(...$arguments);
    }

    /**
     * Execute the routers
     */
    public static function execute()
    {
        self::$collection->run();
    }
}