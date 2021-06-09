<?php

namespace MinasRouter\Router;

use MinasRouter\Router\RouteGroups;
use MinasRouter\Router\RouteCollection;
use MinasRouter\Router\Middlewares\MiddlewareRoute;

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
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function post(String $uri, $callback)
    {
        return self::$collection->addRoute("POST", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function put(String $uri, $callback)
    {
        return self::$collection->addRoute("PUT", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function patch(String $uri, $callback)
    {
        return self::$collection->addRoute("PATCH", $uri, $callback);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
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
     * @param \Closure|array $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public static function redirect(String $uri, String $redirect, Int $httpCode = 302)
    {
        return self::$collection->addRedirectRoute($uri, $redirect, $httpCode);
    }

    /**
     * @param string $uri
     * @param \Closure|array $callback
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
    public static function match(array $methods, String $uri, $callback)
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
     * Method responsible for creating a 
     * new instance of RouteGroups.
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    private static function newRouteGroup()
    {
        $group = (new RouteGroups(self::$collection));

        self::$collection->defineGroup($group);

        return $group;
    }

    /**
     * Method responsible for setting all global
     * middlewares and identifying them with alias.
     * 
     * @param Array $middlewares
     * 
     * @return void
     */
    public static function globalMiddlewares(Array $middlewares)
    {
        MiddlewareRoute::setMiddlewares($middlewares);
    }

    /**
     * Method responsible for setting the default
     * namespace in a route group.
     * 
     * @param string $namespace
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public static function namespace(String $namespace)
    {
        return self::newRouteGroup()->namespace($namespace);
    }

    /**
     * Method responsible for setting the default
     * prefix in a route group.
     * 
     * @param string $prefix
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public static function prefix(String $prefix)
    {
        return self::newRouteGroup()->prefix($prefix);
    }

    /**
     * Method responsible for setting the default
     * middleware in a route group.
     * 
     * @param string $middleware
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public static function middleware($middleware)
    {
        return self::newRouteGroup()->middlewares($middleware);
    }

    /**
     * Method responsible for setting the default
     * name in a route group.
     * 
     * @param string $name
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public static function name(String $name)
    {
        return self::newRouteGroup()->name($name);
    }
}
