<?php

namespace MinasRouter\Http;

class Request
{
    private $fullUrl;

    private $httpMethod;

    private $data;

    private $contentType;

    private $params;

    private $statusCode;

    private $headers;

    public function __construct(
        String $fullUrl, 
        String $route, 
        Array $routeParams, 
        Int $status = 200, 
        String $contentType = 'text/html'
    ) {
        $this->fullUrl = $fullUrl . $_SERVER['REQUEST_URI'];

        $this->statusCode = $status;
        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';

        $this->headers = getallheaders();

        $this->setContentType($contentType);
        $this->resolveRouteData($route, $routeParams);

        if($this->httpMethod != 'GET') {
            $this->setData();
        }
    }

    public function getParams()
    {
        return $this->params ?? $this->data;
    }

    protected function resolveRouteData(String $route, Array $routeParams)
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

    public function getMethod()
    {
        return $this->httpMethod;
    }

    protected function setContentType(String $contentType): void
    {
        $this->contentType = $contentType;

        $this->setHeader('Content-Type', $contentType);
    }

    protected function setHeader(String $key, String $value): void
    {
        $this->headers[$key] = $value;
    }

    protected function sendHeaders(): void
    {
        http_response_code($this->statusCode);

        foreach ($this->headers as $header => $value) {
            header("{$header}: {$value}");
        }
    }

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
}