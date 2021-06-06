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
        Array $routeParams
    ) {
        $this->fullUrl = $fullUrl . $_SERVER['REQUEST_URI'];

        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->queryStrings = $_GET;

        $this->headers = getallheaders();

        $this->resolveRouteData($route, $routeParams);

        if($this->httpMethod != 'GET') {
            $this->setData();
        }
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
        if($name && isset($this->queryStrings[$name])) {
            return $this->queryStrings[$name];
        }

        return $this->queryStrings;
    }

    /**
     * Method responsible for defining route data
     * 
     * @param string $route
     * @param array $routeParams
     * 
     * @return void
     */
    protected function resolveRouteData(String $route, Array $routeParams): void
    {
        $params = parse_url($this->fullUrl);

        $diff = array_diff(explode('/', $params['path']), explode('/', $route));
        
        sort($diff);

        if(!empty($diff)) {
            foreach($routeParams as $index => $param) {
                if(!isset($diff[$index])) return;

                if($this->httpMethod != 'GET') {
                    $this->params[$param] = $diff[$index];
                    continue;
                }

                $this->data[$param] = $diff[$index];
            }
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
        $post = filter_input_array(INPUT_POST, FILTER_DEFAULT);

        if (!empty($post['_method']) && in_array($post['_method'], ["PUT", "PATCH", "DELETE"])) {
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

        if (in_array($this->httpMethod, ["PUT", "PATCH", "DELETE"]) && !empty($_SERVER['CONTENT_LENGTH'])) {
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
        if(!$except) {
            return $this->data;
        }
        
        $except = explode(',', $except);

        dd($except);

        $except = array_map(function($excession) {
            return trim(rtrim($excession));
        }, $except);

        foreach($except as $excession) {
            if(!isset($this->data[$excession])) return;

            unset($this->data[$excession]);
        }

        return $this->data;
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
        if($header && isset($this->headers[$header])) {
            return $this->headers[$header];
        }

        return $this->headers;
    }
}