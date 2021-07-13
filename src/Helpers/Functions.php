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

    public function getStructuredRoute(String $routerName, $params)
    {
        $params = (array) func_get_arg(1);

        if(!$router = $this->get($routerName)) {
            return null;
        }

        $originalRoute = $router->getOriginalRoute();

        preg_match_all("/{\w+\??}/", $originalRoute, $matches);
  
        if(empty($matches[0])) {
            return $originalRoute;
        }

        if(count($matches[0]) != count($params)) {
            return null;
        }

        foreach($matches[0] as $index => $match) {
            $paramForReplace = isset($params[$index]) ? $params[$index] : 'undefined';

            $originalRoute = str_replace($match, $paramForReplace, $originalRoute);
        }

        return $originalRoute;
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