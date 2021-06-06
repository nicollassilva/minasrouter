<?php

namespace MinasRouter\Router;

use MinasRouter\Router\RouteCollection;

abstract class Route extends RouteCollection
{
    /** @var string */
    protected static $projectUrl;

    /** @var object */
    public static $collection;

    /** @var string */
    protected static $separator = '@';

    /** @var object */
    protected $obMiddleware;

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
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function post(String $uri, $callback)
    {
        return self::$collection->addRoute("POST", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function put(String $uri, $callback)
    {
        return self::$collection->addRoute("PUT", $uri, $callback);
    }
    
    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function patch(String $uri, $callback)
    {
        return self::$collection->addRoute("PATCH", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function delete(String $uri, $callback)
    {
        return self::$collection->addRoute("DELETE", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function any(String $uri, $callback)
    {
        return self::$collection->addMultipleRoutes($uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouterManager
     */
    public static function match(Array $methods, String $uri, $callback)
    {
        return self::$collection->addMultipleRoutes($uri, $callback, $methods);
    }

    public static function __callStatic($method, $arguments)
    {
        return self::$collection->{$method}(...$arguments);
    }

    /**
     * Execute the routers
     * 
     * @return void
     */
    public static function execute()
    {
        self::$collection->run();
    }

    public static function middlewares(Array $middlewares)
    {

    }
}