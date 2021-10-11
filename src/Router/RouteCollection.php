<?php

namespace MinasRouter\Router;

use MinasRouter\Router\RouteGroups;
use MinasRouter\Traits\RouterHelpers;
use MinasRouter\Traits\RouteManagement;
use MinasRouter\Exceptions\NotFoundException;
use MinasRouter\Exceptions\BadMethodCallException;
use MinasRouter\Exceptions\MethodNotAllowedException;
use MinasRouter\Router\Middlewares\MiddlewareCollection;
use MinasRouter\Exceptions\BadMiddlewareExecuteException;

class RouteCollection
{
    use RouteManagement, RouterHelpers;

    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $currentUri;

    /** @var object */
    protected $currentGroup;

    /** @var object */
    protected $currentRoute;

    /** @var string */
    protected $requestMethod;

    /** @var string */
    protected $actionSeparator;

    /** @var array */
    protected $httpCodes = [
        "badRequest" => 400,
        "notAllowed" => 403,
        "notFound" => 404,
        "methodNotAllowed" => 405,
        "notImplemented" => 501,
        "redirect" => 302
    ];

    /** @var array */
    protected $routes = [
        "GET" => [],
        "POST" => [],
        "PUT" => [],
        "PATCH" => [],
        "DELETE" => [],
        "REDIRECT" => []
    ];

    /** @var array */
    protected $formSpoofingMethods = ["PUT", "PATCH", "DELETE"];

    public function __construct(String $separator, String $baseUrl)
    {
        $this->baseUrl = $baseUrl;
        $this->actionSeparator = $separator;
        $this->currentUri = filter_input(INPUT_GET, "route", FILTER_DEFAULT) ?? "/";
    }

    /**
     * Method responsible for defining the 
     * group of current routes.
     * 
     * @param null|\MinasRouter\Router\RouteGroups $group = null
     * 
     * @return void
     */
    public function defineGroup(?RouteGroups $group = null): void
    {
        $this->currentGroup = $group;
    }

    /**
     * Method responsible for returning the
     * current route.
     * 
     * @return null|\MinasRouter\Router\RouteManager
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
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
        $uri = $this->resolveRouterUri($uri);

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

    /**
     * Method responsible for adding a redirect route.
     * 
     * @param string $uri
     * @param string $redirect
     * @param int $httpCode
     * 
     * @return void
     */
    public function addRedirectRoute(String $uri, String $redirect, Int $httpCode): void
    {
        $uri = $this->resolveRouterUri($uri);

        $this->routes["REDIRECT"][$uri] = $this->redirectRouterData($redirect, $httpCode);
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

    /**
     * Method responsible for returning a route
     * by the name attribute.
     * 
     * @param string $routeName
     * @param null|string $httpMethod = null
     * 
     * @return \MinasRouter\Router\RouteManager|null
     */
    public function getByName(String $routeName, $httpMethod = null): ?RouteManager
    {
        $routes = $this->routes;
        $httpMethod = !$httpMethod ?: strtoupper($httpMethod);

        unset($routes["REDIRECT"]);

        if ($httpMethod && isset($this->routes[$httpMethod])) {
            $routes = $this->routes[$httpMethod];
        }

        if (!is_array($routes)) return null;

        $soughtRoute = null;

        foreach ($routes as $verb) {
            if (!$this->instanceOf($verb, RouteManager::class)) {
                foreach ($verb as $route) {
                    if ($route->getName() === $routeName) {
                        $soughtRoute = $route;
                        break;
                    }
                }
            } else {
                if ($verb->getName() === $routeName) {
                    $soughtRoute = $verb;
                    break;
                }
            }
        }

        return $soughtRoute;
    }

    /**
     * Method responsible for verifying if the
     * object is an instance of class.
     * 
     * @param mixed $object
     * 
     * @return bool
     */
    protected function instanceOf($object, $class)
    {
        return is_a($object, $class);
    }

    /**
     * Method responsible for redirecting to an
     * existing route or a uri.
     * 
     * @param object|array $route
     * @param bool $permanent = false
     */
    protected function redirectRoute(array $routes, $permanent = false)
    {
        $redirectRoute = $this->baseUrl;

        [$routeObject, $route] = $routes;

        if ($this->instanceOf($routeObject, RouteManager::class)) {
            $redirectRoute .= rtrim($routeObject->getRoute(), '(\/)?');
        } else {
            $redirectRoute .= $this->resolveRouterUri($route["redirect"]);
        }

        header("Location: {$redirectRoute}", true, $permanent ? 301 : $route["httpCode"]);
        exit();
    }

    /**
     * Method responsible for formSpoofing the
     * HTTP verbs coming from the form.
     * 
     * @return null|void
     */
    protected function resolveRequestMethod()
    {
        $method = $_SERVER["REQUEST_METHOD"];

        if (isset($_POST["_method"]) && in_array($_POST["_method"], $this->formSpoofingMethods)) {
            $this->requestMethod = $_POST["_method"];
            return null;
        }

        $this->requestMethod = $method;
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

        if (array_key_exists($currentRoute = $this->resolveRouterUri($this->currentUri), $this->routes["REDIRECT"])) {
            $route = $this->routes["REDIRECT"][$currentRoute];
            $redirectRoute = $this->getByName($route["redirect"]);

            $this->redirectRoute(
                [$redirectRoute, $route],
                $route["permanent"]
            );
        }

        $this->resolveRequestMethod();

        foreach ($this->routes[$this->requestMethod] as $route) {
            if (preg_match("~^" . $route->getRoute() . "$~", $this->currentUri)) {
                $this->currentRoute = $route;
            }
        }

        $this->dispatchRoute();
    }

    /**
     * Returns the URI based on group prefix.
     * 
     * @param string $uri
     * 
     * @return string
     */
    protected function resolveRouterUri(String $uri): String
    {
        $uri = $this->fixRouterUri($uri);

        if ($this->instanceof($this->currentGroup, RouteGroups::class) && $this->currentGroup->prefix) {
            $prefix = $this->fixRouterUri($this->currentGroup->prefix);

            return $prefix . $uri;
        }

        return $uri;
    }

    /**
     * Method responsible for performing
     * route actions.
     * 
     * @return null|\Closure
     */
    protected function dispatchRoute(): ?\Closure
    {
        if (!$route = $this->currentRoute) {
            if($fallbackRoute = $this->getByName('fallback')) {
                return $this->executeRoute($fallbackRoute);
            }
            
            $this->setHttpCode($this->httpCodes["notFound"]);

            $this->throwException(
                "notFound",
                NotFoundException::class,
                "Route [%s] with method [%s] not found.",
                $_SERVER["REQUEST_URI"],
                $this->requestMethod
            );
        }

        $this->executeMiddlewares($route);

        [$controller, $method] = $route->getCompleteAction();

        if ($this->instanceOf($method, \Closure::class)) {
            $this->setHttpCode();
            return call_user_func($route->getAction(), ...$route->closureReturn());
        }

        $obController = $this->resolveRouteController($controller);

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

    /**
     * Responsible for execute the route
     * 
     * @param RouteManager $route
     * 
     * @return mixed|false
     */
    protected function executeRoute(RouteManager $route)
    {
        return call_user_func($route->getAction(), ...$route->closureReturn());
    }

    /**
     * Method responsible for checking if the controller
     * class exists and returns an instance of it.
     * 
     * @param string $controller
     */
    protected function resolveRouteController(String $controller)
    {
        if (!class_exists($controller)) {
            $this->setHttpCode($this->httpCodes["badRequest"]);

            $this->throwException(
                "badRequest",
                BadMethodCallException::class,
                "Class [%s] doesn't exist.",
                $controller
            );
        }

        return new $controller;
    }

    /**
     * Method responsible for executing
     * the middlewares of the current route.
     * 
     * @return mixed|false|void
     */
    protected function executeMiddlewares(RouteManager $route)
    {
        if ($this->instanceOf($route->getMiddleware(), MiddlewareCollection::class)) {

            $route->getMiddleware()->setRequest($route->request());

            if (!$route->getMiddleware()->execute()) {
                $this->setHttpCode($this->httpCodes["notFound"]);

                $this->throwException(
                    "notFound",
                    BadMiddlewareExecuteException::class,
                    "Some middleware has not approved your request."
                );
            }
        }
    }

    /**
     * Method responsible for returning an
     * http method by slug.
     * 
     * @param string $slug
     * 
     * @return null|int
     */
    protected function getHttpCode(String $slug)
    {
        if (!isset($this->httpCodes[$slug])) return null;

        return $this->httpCodes[$slug];
    }

    /**
     * Method responsible for rendering
     * the http method on the page.
     * 
     * @param int $code = 200
     * 
     * @return void
     */
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
    public function getRoutesOf(String $method)
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method])) return null;

        return $this->routes[$method];
    }
}
