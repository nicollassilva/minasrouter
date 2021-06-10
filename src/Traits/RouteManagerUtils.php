<?php

namespace MinasRouter\Traits;

trait RouteManagerUtils
{
    public abstract function setWhereData(String $key, String $value);
    public abstract function setName(String $name);
    public abstract function getDefaultName();

    /**
     * Method responsible for defining the regular
     * expressions of dynamic parameters.
     * 
     * @param array $matches
     * 
     */
    public function where(array $matches)
    {
        array_map(function ($key, $value) {
            $this->setWhereData($key, $value);
        }, array_keys($matches), $matches);

        return $this;
    }

    /**
     * Alias of the where method, but only for one parameter
     * 
     * @param string $param
     * @param string $value
     * 
     */
    public function whereParam(String $param, String $value)
    {
        $this->setWhereData($param, $value);

        return $this;
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as a number.
     * 
     * @param string $param
     * 
     */
    public function whereNumber(String $param)
    {
        return $this->whereParam($param, "[0-9]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha characters.
     * 
     * @param string $param
     * 
     */
    public function whereAlpha(String $param)
    {
        return $this->whereParam($param, "[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha numeric.
     * 
     * @param string $param
     * 
     */
    public function whereAlphaNumeric(String $param)
    {
        return $this->whereParam($param, "[a-zA-ZÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖßÙÚÛÜÝŸÑàáâãäåçèéêëìíîïðòóôõöùúûüýÿñ0-9]+");
    }

    /**
     * Method responsible for defining a
     * dynamic parameter as alpha numeric.
     * 
     * @param string $param
     * 
     */
    public function whereUuid(String $param)
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
     */
    public function name(String $name, Bool $ignoreDefault = false)
    {
        if($ignoreDefault) {
            $this->setName($name);
        } else {
            $this->setName($this->getDefaultName() . $name);
        }

        return $this;
    }

    /**
     * Alias of the name method.
     * 
     * @param string $name
     * 
     */
    public function as(String $name)
    {
        return $this->name($name);
    }
}