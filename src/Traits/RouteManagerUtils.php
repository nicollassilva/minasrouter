<?php

namespace MinasRouter\Traits;

use MinasRouter\Router\RouteManager;

trait RouteManagerUtils
{
    /**
     * Method responsible for defining the regular
     * expressions of dynamic parameters.
     * 
     * @param array $matches
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function where(array $matches): RouteManager
    {
        array_map(function ($key, $value) {
            $this->where[$key] = $value;
        }, array_keys($matches), $matches);

        return $this;
    }

    /**
     * Alias of the where method, but only for one parameter
     * 
     * @param string $param
     * @param string $value
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereParam(String $param, String $value): RouteManager
    {
        $this->where[$param] = $value;

        return $this;
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as a number.
     * 
     * @param string $param
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereNumber(String $param): RouteManager
    {
        return $this->whereParam($param, "[0-9]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha characters.
     * 
     * @param string $param
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereAlpha(String $param): RouteManager
    {
        return $this->whereParam($param, "[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha numeric.
     * 
     * @param string $param
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereAlphaNumeric(String $param): RouteManager
    {
        return $this->whereParam($param, "[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ0-9]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha numeric.
     * 
     * @param string $param
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function whereUuid(String $param): RouteManager
    {
        return $this->whereParam($param, "(?i)[0-9a-f]{8}-[0-9a-f]{4}-[0-5][0-9a-f]{3}-[089ab][0-9a-f]{3}-[0-9a-f]{12}");
    }

    /**
     * Set the name of route. The second parameter
     * when true will ignore the group prefix.
     * 
     * @param string $name
     * @param bool $ignoreDefault
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function name(String $name, Bool $ignoreDefault = false): RouteManager
    {
        if($ignoreDefault) {
            $this->name = $name;
        } else {
            $this->name = $this->defaultName . $name;
        }

        return $this;
    }

    /**
     * Alias of the name method.
     * 
     * @param string $name
     * 
     * @return \MinasRouter\Router\RouteManager
     */
    public function as(String $name): RouteManager
    {
        return $this->name($name);
    }
}