<?php

namespace MinasRouter\Router;

use MinasRouter\Http\Request;
use MinasRouter\Traits\RouteManagerUtils;
use MinasRouter\Router\Middlewares\MiddlewareCollection;

class RouteManager
{
    use RouteManagerUtils;

    /** @var string */
    protected $separator;

    /** @var string */
    protected $name;

    /** @var string */
    private $defaultName;

    /** @var string */
    private $defaultNamespace;

    /** @var object */
    protected $group;

    /** @var string */
    protected $route;

    /** @var string */
    protected $originalRoute;

    /** @var string */
    protected $fullUrl;

    /** @var string */
    protected $defaultRegex = "[^/]+";

    /** @var object */
    protected $middleware;

    /** @var string */
    protected $action;

    /** @var string */
    protected $handler;

    /** @var array */
    protected $where = [];

    /** @var object */
    protected $request;

    public function __construct($fullUrl, $uri, $callback, String $separator, ?RouteGroups $group = null)
    {
        $this->fullUrl = $fullUrl;
        $this->route = $uri;
        $this->originalRoute = $uri;
        $this->separator = $separator;
        $this->group = $group;

        $this->request = new Request(
            $this->fullUrl,
            $this->route,
            $this->foundParameters(true)
        );

        $this->compileGroupData();
        $this->compileAction($callback);
    }

    /**
     * Method responsible for returning
     * the current group.
     * 
     * @return null|\MinasRouter\Router\RouteGroups
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Method responsible for defining the
     * settings of the group the is part of.
     * 
     * @return null
     */
    private function compileGroupData()
    {
        if(!is_a($this->group, RouteGroups::class)) return null;

        $this->defaultName = $this->name = $this->group->name;
        $this->defaultNamespace = $this->group->namespace;

        if(is_a($this->group->middlewares, MiddlewareCollection::class)) {
            $this->middleware = $this->group->middlewares;
        }

        return null;
    }

    /**
     * Method responsible for returning the filtered route.
     * 
     * @return string
     */
    public function getRoute(): String
    {
        return $this->filteredRoute();
    }

    /**
     * Method responsible for filtering the route,
     * inserting regular expressions in place of dynamic parameters.
     * 
     * @return string
     */
    protected function filteredRoute(): String
    {
        $parameters = $this->foundParameters();

        foreach ($parameters as $parameter) {
            $realRegex = $this->getWhere($parameter[1]) ?? $this->defaultRegex;

            if (preg_match("/\?/", $parameter[0])) {
                $this->route = str_replace("/{$parameter[0]}", "(\/)?({$realRegex})?", $this->route);
                continue;
            }

            $this->route = str_replace("{$parameter[0]}", "({$realRegex})", $this->route);
        }

        return $this->route . '(\/)?';
    }

    /**
     * Method responsible for returning the regular
     * expression of a dynamic parameter.
     * 
     * @param string $param
     * 
     * @return null|string
     */
    protected function getWhere(String $param): ?String
    {
        if (!isset($this->where[$param])) return null;

        return $this->where[$param];
    }

    /**
     * Method responsible for returning all dynamic parameters.
     * 
     * @return array
     */
    protected function foundParameters($wordOnly = false, $originalRoute = false): array
    {
        $route = $originalRoute ? $this->originalRoute : $this->route;

        preg_match_all("~\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\??\}~x", $route, $params, PREG_SET_ORDER);

        if ($wordOnly) {
            $params = array_map(function ($param) {
                return $param[1];
            }, $params);
        }

        return $params;
    }

    /**
     * Method responsible for returning the parameters
     * for the route action.
     * 
     * @return array
     */
    public function closureReturn(): array
    {
        $getParams = $this->request()->getParams();
        $dinamycParameters = array_fill_keys($this->foundParameters(true, true), null);
        
        $params = array_values(
            array_merge($dinamycParameters, $getParams)
        );

        return [
            ...$params,
            $this->request()
        ];
    }

    /**
     * Method responsible for separating and defining the class and method,
     * or the anonymous function of the route.
     * 
     * @param string
     */
    private function compileAction($callback): void
    {
        if ($callback instanceof \Closure) {
            $this->action = $callback;
            return;
        }

        if (is_string($callback)) {
            [$handler, $action] = explode($this->separator, $callback);
        }

        if (is_array($callback)) {
            $handler = $callback[0];
            $action = $callback[1];
        }

        $this->handler = $this->resolveHandler($handler);
        $this->action = $action;
    }
    
    /**
     * Method responsible for returning the correct controller,
     * including the namespace of a group.
     * 
     * @param string $handler
     * 
     * @return string
     */
    private function resolveHandler(String $handler)
    {
        if(!$this->defaultNamespace) {
            return $handler;
        }

        $namespace = $this->parseDefaultString($this->defaultNamespace);
        
        return $namespace . $this->parseDefaultString($handler);
    }

    /**
     * Method responsible for applying the default
     * rule in the controller string.
     * 
     * @param string $handler
     * 
     * @return string
     */
    private function parseDefaultString(String $handler)
    {
        if (!preg_match("/^\\\/", $handler, $match)) {
            $handler = "\\{$handler}";
        }

        if (preg_match("/\/$/", $handler, $match)) {
            $handler = rtrim($handler, "/");
        }

        return $handler;
    }

    /**
     * Get the route name.
     * 
     * @return null|string
     */
    public function getName(): ?String
    {
        return $this->name;
    }

    /**
     * Get the route action.
     * 
     * @return mixed|\Closure|string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get the route action and handler.
     * 
     * @return array
     */
    public function getCompleteAction()
    {
        return [
            $this->getHandler(),
            $this->getAction()
        ];
    }

    /**
     * Get the route handler.
     * 
     * @return mixed|\Closure|string
     */
    public function getHandler()
    {
        return $this->handler;
    }

    /**
     * Get the request route.
     * 
     * @return \MinasRouter\Http\Request
     */
    public function request(): Request
    {
        return $this->request;
    }

    /**
     * Get the middleware route.
     * 
     * @return null|\MinasRouter\Router\Middlewares\MiddlewareCollection
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Method responsible for defining the middlewares of the route.
     * 
     * @param string|array $middleware
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function middleware($middleware): RouteManager
    {
        if(is_a($this->middleware, MiddlewareCollection::class)) {
            $middleware = $this->middleware->storeMiddleware($middleware);
        }

        $this->middleware = new MiddlewareCollection($middleware);

        return $this;
    }
}
