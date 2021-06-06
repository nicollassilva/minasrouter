<?php

namespace MinasRouter\Router;

use MinasRouter\Traits\RouteManagement;
use MinasRouter\Exceptions\NotFoundException;
use MinasRouter\Exceptions\BadMethodCallException;

class RouteCollection
{
    use RouteManagement;

    protected $actionSeparator;

    protected $currentUri;

    protected $baseUrl;

    protected $currentRoute;

    protected $httpCodes = [
        "badRequest" => 400,
        "notAllowed" => 403,
        "notFound" => 404,
        "methodNotAllowed" => 405,
        "notImplemented" => 501,
        "redirect" => 302
    ];

    protected $routes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
        "ANY" => [],
        "MATCH" => []
    ];

    public function __construct(String $separator, String $baseUrl)
    {
        $this->actionSeparator = $separator;
        $this->baseUrl = $baseUrl;
        $this->currentUri = filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? '/';
    }

    public function addRoute(String $method, $uri, $callback)
    {
        if(array_key_exists($method, $this->routes)) {
            return $this->routes[$method][$uri] = $this->addRouter($uri, $callback);
        }
    }

    public function addMultipleRoutes(String $uri, $callback, ?Array $methods = null)
    {
        if(!$methods) {
            $methods = array_keys($this->routes);
        }

        array_map(function($method) use ($uri, $callback) {
            $this->routes[$method][$uri] = $this->addRouter($uri, $callback);
        }, $methods);
    }
    
    public function __call($method, $arguments)
    {
        throw new BadMethodCallException(sprintf(
            "Method [%s::%s] doesn't exist.", static::class, $method
        ), $this->httpCodes["badRequest"]);
    }

    public function run()
    {
        $this->currentRoute = null;

        foreach ($this->routes[$_SERVER['REQUEST_METHOD']] as $route) {
            if (preg_match("~^" . $route->getRoute() . "$~", $this->currentUri)) {
                $this->currentRoute = $route;
            }
        }

        return $this->dispatchRoute();
    }

    public function dispatchRoute()
    {
        if(!$route = $this->currentRoute) {
            throw new NotFoundException(sprintf(
                    "Route [%s] with method [%s] not found,", $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']
                ), $this->httpCodes['notFound']);
        }

        $controller = $route->getHandler();
        $method = $route->getAction();

        if($method instanceof \Closure) {
            return call_user_func($route->getAction(), ...$route->closureReturn());
        }

        if(!class_exists($controller)) {
            throw new BadMethodCallException(sprintf(
                "Class [%s::%s] doesn't exist.", $controller, $method
            ), $this->httpCodes["badRequest"]);
        }

        $obController = new $controller;

        if(!method_exists($obController, $method)) {
            throw new MethodNotAllowedException(sprintf(
                "Method [%s::%s] doesn't exist.", $controller, $method
            ), $this->httpCodes["methodNotAllowed"]);
        }

        $obController->{$method}(...$route->closureReturn());
    }
}