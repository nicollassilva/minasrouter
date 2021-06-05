<?php

namespace MinasRouter\Traits;

use MinasRouter\Router\RouteManager;

trait RouteManagement
{
    public function addRouter(String $uri, $callback)
    {
        return $this->newRouter($uri, $callback);
    }

    public function newRouter($uri, $callback)
    {
        return new RouteManager($this->baseUrl, $uri, $callback, $this->actionSeparator);
    }
}