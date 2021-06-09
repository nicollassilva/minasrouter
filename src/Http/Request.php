<?php

namespace MinasRouter\Http;

class Request
{
    private $fullUrl;

    private $httpMethod;

    private $data = [];

    private $queryStrings;

    private $params;

    private $headers;

    public function __construct(
        String $fullUrl,
        String $route,
        array $routeParams
    ) {
        $this->fullUrl = $fullUrl . ($_SERVER['REQUEST_URI'] ?? '/');

        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->queryStrings = $_GET;
        
        if(isset($this->queryStrings["route"])) {
            unset($this->queryStrings["route"]);
        }

        $this->headers = $this->resolveHeaders();

        $this->resolveRouteData($route, $routeParams);

        if ($this->httpMethod != 'GET') {
            $this->setData();
        }
    }

    /**
     * Method responsible for bringing
     * all request headers.
     * 
     * @return array
     */
    protected function resolveHeaders()
    {
        $headers = [];

        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $index = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$index] = $value;
            }
        }

        return $headers;
    }

    /**
     * Method responsible for returning the
     * route's dynamic parameters.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params ?? $this->data;
    }

    /**
     * Method responsible for returning one or all
     * data coming from the params query ($_GET).
     * 
     * @return null|string $name = null
     * 
     * @return array|string
     */
    public function getQueryString(?String $name = null)
    {
        if ($name && isset($this->queryStrings[$name])) {
            return $this->queryStrings[$name];
        }

        return $this->queryStrings;
    }

    /**
     * Returns a property that does not exist in the class,
     * usually they are indexes of the route array. Returns null
     * if this index/property does not exist.
     * 
     * @param string $data
     * 
     * @return string|null|array
     */
    public function __get(String $data)
    {
        if(isset($this->data[$data])) {
            return $this->data[$data];
        }

        if(isset($this->queryStrings[$data])) {
            return $this->queryStrings[$data];
        }

        if(isset($this->params[$data])) {
            return $this->params[$data];
        }

        return null;
    }

    /**
     * Method responsible for defining route data
     * 
     * @param string $route
     * @param array $routeParams
     * 
     * @return void
     */
    protected function resolveRouteData(String $route, array $routeParams): void
    {
        $params = parse_url($this->fullUrl);

        $diff = array_diff(explode('/', $params['path']), explode('/', $route));

        $diff = array_values($diff);

        if (!empty($diff)) {
            foreach ($routeParams as $index => $param) {
                if (!isset($diff[$index])) return;

                if ($this->httpMethod != 'GET') {
                    $this->params[$param] = rawurldecode($diff[$index]);
                    continue;
                }

                $this->data[$param] = rawurldecode($diff[$index]);
            }
        }

        if(empty($this->data)) {
            $this->data = [];
        }

        if(empty($this->params)) {
            $this->params = [];
        }
    }

    /**
     * Method responsible for assigning and handling
     * the data coming from the web form.
     * 
     * @return void
     */
    protected function setData()
    {
        $enableFormSpoofing = ["PUT", "PATCH", "DELETE"];

        $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (!empty($post['_method']) && in_array($post['_method'], $enableFormSpoofing)) {
            $this->httpMethod = $post['_method'];
            $this->data = $post;

            unset($this->data["_method"]);
            return;
        }
        if ($this->httpMethod == "POST") {
            $this->data = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            unset($this->data["_method"]);
            return;
        }

        if (in_array($this->httpMethod, $enableFormSpoofing) && !empty($_SERVER['CONTENT_LENGTH'])) {
            parse_str(file_get_contents('php://input', false, null, 0, $_SERVER['CONTENT_LENGTH']), $putPatch);
            $this->data = $putPatch;

            unset($this->data["_method"]);
            return;
        }

        $this->data = [];
        return;
    }

    /**
     * Return the httpMethod.
     * 
     * @return string
     */
    public function getMethod()
    {
        return $this->httpMethod;
    }

    /**
     * Method responsible for returning all request data.
     * 
     * @param null|string $except = null
     * 
     * @return array|null
     */
    public function all(?String $except = null)
    {
        if (!$except) {
            return $this->data;
        }

        $allWithExcession = $this->data;
        $except = explode(',', $except);

        $except = array_map(function ($excession) {
            return trim(rtrim($excession));
        }, $except);

        foreach ($except as $excession) {
            if (!isset($this->data[$excession])) return;

            unset($allWithExcession[$excession]);
        }

        return $allWithExcession;
    }

    /**
     * Method responsible for returning only
     * the data passed in parameter.
     * 
     * @param string $only
     * 
     * @return array|string
     */
    public function only(String $only)
    {
        $result = [];
    }

    /**
     * Method responsible for returning one or all header data.
     * 
     * @param string $header = null
     * 
     * @return array|string
     */
    public function getHeaders(?String $header = null)
    {
        if ($header && isset($this->headers[$header])) {
            return $this->headers[$header];
        }

        return $this->headers;
    }
}
