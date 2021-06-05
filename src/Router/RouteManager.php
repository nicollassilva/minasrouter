<?php

namespace MinasRouter\Router;

use MinasRouter\Http\Request;

class RouteManager
{

    /** @var string */
    protected $separator;
    
    /** @var string */
    protected $name;

    /** @var string */
    protected $route;

    /** @var string */
    protected $originalRoute;

    /** @var string */
    protected $fullUrl;

    /** @var string */
    protected $defaultRegex = "[^/]+";

    /** @var array */
    protected $middlewares = [];

    /** @var string */
    protected $action;

    /** @var string */
    protected $handler;

    /** @var array */
    protected $where = [];

    /** @var object */
    protected $request;

    public function __construct($fullUrl, $uri, $callback, String $separator)
    {
        $this->fullUrl = $fullUrl;
        $this->route = $uri;
        $this->originalRoute = $uri;
        $this->separator = $separator;

        $this->request = new Request(
                $this->fullUrl, $this->route, $this->foundParameters(true)
            );
        
        $this->compileAction($callback);
    }

    /**
     * Method responsible for returning the filtered route.
     * 
     * @return string
     */
    public function getRoute()
    {
        return $this->filteredRoute();
    }

    /**
     * Method responsible for filtering the route,
     * inserting regular expressions in place of dynamic parameters.
     * 
     * @return string
     */
    protected function filteredRoute()
    {
        $parameters = $this->foundParameters();

        foreach($parameters as $parameter) {
            $realRegex = $this->getWhere($parameter[1]) ?? $this->defaultRegex;

            if(preg_match("/\?/", $parameter[0])) {
                $this->route = str_replace("/{$parameter[0]}", "(\/)?({$realRegex})?", $this->route);
                continue;
            }
            
            $this->route = str_replace("{$parameter[0]}", "({$realRegex})", $this->route);
        }
    
        return $this->route;
    }

    /**
     * Method responsible for returning the regular
     * expression of a dynamic parameter.
     * 
     * @param string $param
     * 
     * @return null|string
     */
    protected function getWhere(String $param)
    {
        if(!isset($this->where[$param])) return;

        return $this->where[$param];
    }

    /**
     * Method responsible for returning all dynamic parameters.
     * 
     * @return array
     */
    protected function foundParameters($wordOnly = false): Array
    {
        preg_match_all("~\{\s*([a-zA-Z_][a-zA-Z0-9_-]*)\??\}~x", $this->route, $params, PREG_SET_ORDER);

        if($wordOnly) {
            $params = array_map(function($param) {
                return $param[1];
            }, $params);
        }

        return $params;
    }

    public function closureReturn()
    {
        $params = array_values($this->request()->getParams());

        return [
            ...$params,
            $this->request()
        ];
    }

    /**
     * Method responsible for defining the regular
     * expressions of dynamic parameters.
     * 
     * @param array $matches
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function where(Array $matches): RouteManager
    {
        array_map(function($key, $value) {
            $this->where[$key] = $value;
        }, array_keys($matches), $matches);

        return $this;
    }

    /**
     * Alias of the where method, but only for one parameter
     * 
     * @param string $param
     * @param string $value
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereParam(String $param, String $value): RouteManager
    {
        $this->where[$param] = $value;

        return $this;
    }

    /**
     * Method responsible for separating and defining the class and method,
     * or the anonymous function of the route.
     * 
     * @param string
     */
    private function compileAction($callback): void
    {
        if($callback instanceof \Closure) {
            $this->action = $callback;
            return;
        }
        
        if(is_string($callback)) {
            [$handler, $action] = explode($this->separator, $callback);
        }

        if(is_array($callback)) {
            $handler = $callback[0];
            $action = $callback[1];
        }

        $this->handler = $handler;
        $this->action = $action;
    }

    /**
     * Set the name of route.
     * 
     * @param string
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function name(String $name): RouteManager
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the route name.
     * 
     * @return mixed
     */
    public function getName()
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
    public function request()
    {
        return $this->request;
    }

    /**
     * Alias of the name method.
     * 
     * @param string
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function as(String $name)
    {
        return $this->name($name);
    }
}