<?php

namespace MinasRouter\Router;

use MinasRouter\Router\RouteCollection;
use MinasRouter\Router\Middlewares\MiddlewareCollection;

class RouteGroups
{
    /** @var object */
    protected $collection;

    /** @var string */
    public $namespace;

    /** @var string */
    public $prefix;

    /** @var string */
    public $name;

    /** @var string|array */
    public $middlewares = [];

    public function __construct(RouteCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Set a namespace for the group.
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public function namespace($namespace): RouteGroups
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * Set a prefix for the group.
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public function prefix($prefix): RouteGroups
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set a name for the group.
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public function name($name): RouteGroups
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set a middlewares for the group.
     * 
     * @return \MinasRouter\Router\RouteGroups
     */
    public function middlewares($middlewares): RouteGroups
    {
        $this->middlewares = new MiddlewareCollection($middlewares);

        return $this;
    }

    public function group(\Closure $routeGroups)
    {
        call_user_func($routeGroups);

        return $this->collection->defineGroup();
    }
}
