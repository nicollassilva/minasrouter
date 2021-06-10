<?php

namespace MinasRouter\Traits;

use MinasRouter\Router\RouteGroups;

trait RouterHelpers
{
    protected abstract function getHttpCode(String $slug);
    protected abstract function instanceof($object, $class);
    
    /**
     * Method responsible for removing the bars
     * at the beginning and end of the route.
     * 
     * @param string $uri
     * 
     * @return string
     */
    protected function fixRouterUri(String $uri): String
    {
        if (!preg_match("/^\//", $uri, $match)) {
            $uri = "/{$uri}";
        }

        if (preg_match("/\/$/", $uri, $match)) {
            $uri = rtrim($uri, "/");
        }

        return $uri;
    }

    /**
     * Method responsible for throwing a new exception.
     * 
     * @param string $httpCode
     * @param string $exception
     * @param string $message = ""
     * @param string|array|null ...$sprints
     */
    private function throwException(String $httpCode, String $exception, String $message = "", ...$sprints): \Exception
    {
        throw new $exception(
            sprintf($message, ...$sprints),
            $this->getHttpCode($httpCode)
        );
    }
}
