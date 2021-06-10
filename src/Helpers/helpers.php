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
}