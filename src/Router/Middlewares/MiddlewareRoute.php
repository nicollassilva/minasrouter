<?php

namespace MinasRouter\Router\Middlewares;

abstract class MiddlewareRoute
{
    /** @var array */
    protected static $setMiddlewares = [];

    /**
     * Method responsible for setting middlewares and their alias.
     * 
     * @param array|string|null $middlewares
     * 
     * @return void
     */
    public static function setMiddlewares($middlewares)
    {
        self::$setMiddlewares = $middlewares;
    }

    /**
     * Return the set Middlewares.
     * 
     * @return array
     */
    public static function getSetMiddlewares()
    {
        return self::$setMiddlewares;
    }
}