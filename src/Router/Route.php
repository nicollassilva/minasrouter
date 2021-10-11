<?php

namespace MinasRouter\Router;

use MinasRouter\Traits\RouteUtils;
use MinasRouter\Router\RouteCollection;

abstract class Route extends RouteCollection
{
    use RouteUtils;

    /** @var string */
    protected static $projectUrl;

    /** @var string */
    protected static $separator = '@';

    /** @var object */
    protected $obMiddleware;

    /**
     * @param string $projectUrl
     * @param string $separator = null
     * 
     * @return void
     */
    public static function start(String $projectUrl, String $separator = null): void
    {
        self::$projectUrl = rtrim($projectUrl, '/');

        if ($separator) {
            self::$separator = $separator;
        }

        self::$collection = new parent(
            self::$separator,
            self::$projectUrl
        );
    }

    /**
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function get(String $uri, $callback)
    {
        return self::$collection->addRoute("GET", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function post(String $uri, $callback)
    {
        return self::$collection->addRoute("POST", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function put(String $uri, $callback)
    {
        return self::$collection->addRoute("PUT", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function patch(String $uri, $callback)
    {
        return self::$collection->addRoute("PATCH", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function delete(String $uri, $callback)
    {
        return self::$collection->addRoute("DELETE", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function any(String $uri, $callback)
    {
        return self::$collection->addMultipleHttpRoutes($uri, $callback);
    }

    /**
     * @param string $uri
     * @param string $redirect
     * @param int $httpCode
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function redirect(String $uri, String $redirect, Int $httpCode = 302)
    {
        return self::$collection->addRedirectRoute($uri, $redirect, $httpCode);
    }

    /**
     * @param string $uri
     * @param string $redirect
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function permanentRedirect(String $uri, String $redirect)
    {
        return self::$collection->addRedirectRoute($uri, $redirect, 301);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function match(Array $methods, String $uri, $callback)
    {
        return self::$collection->addMultipleHttpRoutes($uri, $callback, $methods);
    }

    /**
     * @param string $method
     * @param array $arguments
     * 
     * @return void
     */
    public static function __callStatic($method, $arguments)
    {
        return self::$collection->{$method}(...$arguments);
    }

    /**
     * Execute the routers.
     * 
     * @return void
     */
    public static function execute()
    {
        self::$collection->run();
    }

    /**
     * @param \Closure|array|string
     * 
     * @return void
     */
    public static function fallback($callback)
    {
        self::$collection->addRoute("GET", "/404", $callback)->name('fallback');
    }
}
