<?php

use MinasRouter\Helpers\Functions;

if (! function_exists('router')) {
    /**
     * Method responsible for returning a
     * new instance of Functions.
     * 
     * @return \MinasRouter\Helpers\Functions
     */
    function router(): Functions {
        return new Functions;
    }

    /**
     * Method responsible for returning a
     * string route with replaced parameters.
     * 
     * @param string $routeName
     * @param string|array|null $params
     * 
     * @return null|string
     */
    function route(String $routeName, $params = null)
    {
        return router()->getStructuredRoute($routeName, $params);
    }
}