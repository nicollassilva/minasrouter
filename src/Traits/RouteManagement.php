<?php

namespace MinasRouter\Traits;

use MinasRouter\Router\RouteManager;

trait RouteManagement
{
    /**
     * Method responsible for
     * instantiating a new route.
     * 
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    protected function addRouter(String $uri, $callback)
    {
        return $this->newRouter($uri, $callback);
    }

    /**
     * 
     */
    protected function redirectRouterData(String $redirect, Int $http)
    {
        return [
            "redirect" => $redirect,
            "httpCode" => $http,
            "permanent" => !!($http === 301)
        ];
    }

    /**
     * Method responsible for starting a new
     * RouteManager instance.
     * 
     * @param string $uri
     * @param \Closure|array|string $callback
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    protected function newRouter(String $uri, $callback)
    {
        return new RouteManager($this->baseUrl, $uri, $callback, $this->actionSeparator, $this->currentGroup);
    }
}