<?php

namespace MinasRouter\Helpers;

use MinasRouter\Router\Route;

class Functions
{
    /**
     * Method responsible for returning the
     * active RouteCollection instance.
     * 
     * @return null|\MinasRouter\Router\RouteCollection
     */
    protected function collection()
    {
        return Route::$collection;
    }

    /**
     * Method responsible for returning a route.
     * 
     * @param string $name
     * 
     * @return null|\MinasRouter\Router\RouteManager
     */
    public function get(String $routerName)
    {
        return $this->collection()->getByName($routerName);
    }

    /**
     * Method responsible for returning the
     * current route.
     * 
     * @return null|\MinasRouter\Router\RouteManager
     */
    public function current()
    {
        return $this->collection()->getCurrentRoute();
    }
}