<?php

namespace MinasRouter\Traits;

use MinasRouter\Router\RouteManager;

trait RouteManagement
{
    public function addRouter(String $uri, $callback)
    {
        return $this->newRouter($uri, $callback);
    }

    public function addRedirectRouter(String $redirect, Int $http)
    {
        return [
            "redirect" => $redirect,
            "httpCode" => $http,
            "permanent" => !!($http === 301)
        ];
    }

    public function newRouter($uri, $callback)
    {
        // if($this->currentGroup instanceof \MinasRouter\Router\RouteGroups) {
        //     $route->name($this->currentGroup->name);
        //     $route->namespace($this->currentGroup->namespace);
        // }

        return new RouteManager($this->baseUrl, $uri, $callback, $this->actionSeparator, $this->currentGroup);
    }
}