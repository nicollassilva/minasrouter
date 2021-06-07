<?php

namespace MinasRouter\Router;

use MinasRouter\Traits\RouterHelpers;
use MinasRouter\Traits\RouteManagement;
use MinasRouter\Exceptions\NotFoundException;
use MinasRouter\Exceptions\BadMethodCallException;
use MinasRouter\Exceptions\MethodNotAllowedException;

class RouteCollection
{
    use RouteManagement, RouterHelpers;

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
        "REDIRECT" => []
    ];

    public function __construct(String $separator, String $baseUrl)
    {
        $this->actionSeparator = $separator;
        $this->baseUrl = $baseUrl;
        $this->currentUri = filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? "/";
    }

    /**
     * Method responsible for adding a
     * route to an http method.
     * 
     * @param string $method
     * @param string $uri
     * @param array|string|\Closure $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function addRoute(String $method, $uri, $callback)
    {
        $uri = $this->fixRouterUri($uri);

        if (array_key_exists($method, $this->routes)) {
            return $this->routes[$method][$uri] = $this->addRouter($uri, $callback);
        }
    }

    /**
     * Method responsible for adding the same
     * route in more than one http method.
     * 
     * @param string $uri
     * @param array|string|\Closure $callback
     * @param null|array $methods
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function addMultipleHttpRoutes(String $uri, $callback, ?array $methods = null)
    {
        if (!$methods) {
            $methods = array_keys($this->routes);
        }

        $methods = array_map("strtoupper", $methods);

        array_map(function ($method) use ($uri, $callback) {
            $this->routes[$method][$uri] = $this->addRouter($uri, $callback);
        }, $methods);
    }

    public function addRedirectRoute(String $uri, String $redirect, Int $httpCode)
    {
        $this->routes["REDIRECT"][$this->fixRouterUri($uri)] = $this->addRedirectRouter($redirect, $httpCode);
    }

    /**
     * Method responsible for handling method
     * calls that do not exist in the class.
     * 
     * @param string $method
     * @param array $arguments
     * 
     * @return void
     */
    public function __call($method, $arguments)
    {
        $this->throwException(
            "badRequest",
            BadMethodCallException::class,
            "Method [%s::%s] doesn't exist.",
            static::class,
            $method
        );
    }

    public function getRouteByName(String $routeName, $httpMethod = null)
    {
        $routes = $this->routes;

        unset($routes["REDIRECT"]);

        if ($httpMethod) {
            $routes = $this->routes[$httpMethod];
        }

        $route = array_filter($routes, function ($routeInspected) use ($routeName) {
            if(is_array($routeInspected)) {
                foreach($routeInspected as $route) {
                    return $route->getName() === $routeName;
                }
            }

            if($routeInspected instanceof \MinasRouter\Router\RouteManager) {
                return $routeInspected->getName() === $routeName;
            }
        });

        if(!$route) return null;

        $route = array_shift($route);

        if(!$route instanceof \MinasRouter\Router\RouteManager) {
            $route = array_shift($route);
        }

        return $route;
    }

    /**
     * Method responsible for redirecting to an
     * existing route or a uri.
     */
    protected function redirectRoute($route, $permanent = false)
    {
        $redirectRoute = $this->baseUrl;

        if($route instanceof \MinasRouter\Router\RouteManager) {
            $redirectRoute .= $route->getRoute();
        } else {
            $redirectRoute .= $this->fixRouterUri($route["redirect"]);
        }

        header("Location: {$redirectRoute}", true, $permanent ? 301 : 302);
        exit();
    }

    /**
     * Method responsible for listening to browser calls
     * and returning the corresponding route.
     * 
     * @return void
     */
    public function run(): void
    {
        $this->currentRoute = null;

        if (array_key_exists($currentRoute = $this->fixRouterUri($this->currentUri), $this->routes["REDIRECT"])) {
            $route = $this->routes["REDIRECT"][$currentRoute];
            $redirectRoute = $this->getRouteByName($route["redirect"]);

            $this->redirectRoute(
                $redirectRoute ?? $route,
                $route["permanent"]
            );
        }

        foreach ($this->routes[$_SERVER["REQUEST_METHOD"]] as $route) {
            if (preg_match("~^" . $route->getRoute() . "$~", $this->currentUri)) {
                $this->currentRoute = $route;
            }
        }

        $this->dispatchRoute();
    }

    /**
     * Method responsible for performing
     * route actions.
     * 
     * @return null|\Closure
     */
    public function dispatchRoute(): ?\Closure
    {
        if (!$route = $this->currentRoute) {
            $this->setHttpCode($this->httpCodes["notFound"]);

            $this->throwException(
                "notFound",
                NotFoundException::class,
                "Route [%s] with method [%s] not found.",
                $_SERVER["REQUEST_URI"],
                $_SERVER["REQUEST_METHOD"]
            );
        }

        $controller = $route->getHandler();
        $method = $route->getAction();

        if ($method instanceof \Closure) {
            $this->setHttpCode();

            return call_user_func($route->getAction(), ...$route->closureReturn());
        }

        if (!class_exists($controller)) {
            $this->setHttpCode($this->httpCodes["badRequest"]);

            $this->throwException(
                "badRequest",
                BadMethodCallException::class,
                "Class [%s::%s] doesn't exist.",
                $controller,
                $method
            );
        }

        $obController = new $controller;

        if (!method_exists($obController, $method)) {
            $this->setHttpCode($this->httpCodes["methodNotAllowed"]);

            $this->throwException(
                "methodNotAllowed",
                MethodNotAllowedException::class,
                "Method [%s::%s] doesn't exist.",
                $controller,
                $method
            );
        }

        $obController->{$method}(...$route->closureReturn());

        return null;
    }

    protected function getHttpCode(String $slug)
    {
        if (!isset($this->httpCodes[$slug])) return;

        return $this->httpCodes[$slug];
    }

    protected function setHttpCode(Int $code = 200)
    {
        http_response_code($code);
    }

    /**
     * Method responsible for returning all routes
     * from the http method passed in the parameter.
     * 
     * @param string $method
     * 
     * @return null|array
     */
    public function getRouteOf(String $method)
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method])) return null;

        return $this->routes[$method];
    }
}
