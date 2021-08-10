<?php

namespace MinasRouter\Router\Middlewares;

use MinasRouter\Http\Request;
use MinasRouter\Router\Middlewares\MiddlewareRoute;

class MiddlewareCollection
{
    /** @var array */
    protected $queue;

    /** @var array|string */
    protected $middlewares;

    /** @var object */
    protected $currentRequest;

    /** @var int = 0 */
    protected $currentQueueNumber = 0;

    public function __construct($middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Return all middlewares of this route.
     * 
     * @param string $middleware = null
     * 
     * @return string|array
     */
    public function get(String $middleware = null)
    {
        if ($middleware && is_array($this->middlewares) && isset($this->middlewares[$middleware])) {
            return $this->middlewares[$middleware];
        }

        return $this->middlewares;
    }

    /**
     * Method responsible for setting the Route's Request
     * 
     * @param \MinasRouter\Http\Request $request
     * 
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->currentRequest = $request;
    }

    /**
     * Method responsible for setting the
     * middlewares of the route.
     * 
     * @var array $middlewares
     * 
     * @return void
     */
    public function setMiddlewares(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * Store a new middleware and return
     * all middlewares of route.
     * 
     * @param string|array $middleware
     * 
     * @return string|array
     */
    public function storeMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = explode(",", $middleware);
        }

        return $this->resolveLaterMiddleware($middleware);
    }

    /**
     * Remove a middleware of the middleware queue.
     * 
     * @param string|array $middleware
     * 
     * @return string|array
     */
    public function removeMiddleware($middleware)
    {
        if (is_string($middleware)) {
            $middleware = explode(",", $middleware);
        }

        return $this->destroyMiddleware($middleware);
    }

    /**
     * Destroy the middlewares of individual route.
     * 
     * @param array $middleware
     * 
     */
    private function destroyMiddleware(Array $middleware): void
    {
        $currentMiddleware = $this->middlewares;

        foreach ($middleware as $mid) {
            $mid = trim(rtrim($mid));
            
            if (is_string($currentMiddleware)) {
                $currentMiddleware = rtrim(trim(preg_replace("/\s?".$mid."\,?/", "", $currentMiddleware), ' '), ',');
            } else {
                $currentMiddleware = array_values(array_filter($currentMiddleware, fn($middleware) => $middleware != $mid));
            }
        }

        $this->middlewares = $currentMiddleware;
    }

    /**
     * Resolve the middlewares of individual route.
     * 
     * @param array $middleware
     * 
     * @return string|array
     */
    private function resolveLaterMiddleware(Array $middleware)
    {
        $currentMiddleware = $this->middlewares;

        foreach ($middleware as $mid) {
            $mid = trim(rtrim($mid));
            
            if (is_string($currentMiddleware)) {
                $currentMiddleware .= ", {$mid}";
            } else {
                $currentMiddleware[] = $mid;
            }
        }

        return $currentMiddleware;
    }

    /**
     * Alias for execute the middlewares.
     * 
     * @return mixed|bool
     */
    public function execute()
    {
        return $this->executeMiddleware();
    }

    /**
     * Execute the middlewares.
     * 
     * @return mixed|bool
     */
    protected function executeMiddleware()
    {
        $middlewares = $this->middlewares;

        if(is_string($middlewares)) {
            $middlewares = explode(',', $middlewares);
        }

        $this->resolveNestedMiddleware($middlewares);

        return $this->callMiddlewares();
    }

    /**
     * Method responsible for identifying
     * middlewares and instantiating them.
     * 
     * @param array $middlewares
     * 
     * @return void
     */
    protected function resolveNestedMiddleware(Array $middlewares): void
    {
        $this->queue = array_map(function ($middleware) {
            $middleware = trim(rtrim($middleware));

            return $this->instanceMiddleware($middleware);
        }, $middlewares);
    }

    /**
     * Method responsible for instantiating middlewares.
     * 
     * @param string
     * 
     * @return null|object
     */
    protected function instanceMiddleware($middleware)
    {
        if (!preg_match("/\\\/", $middleware)) {
            if (!$middlewareClass = $this->getByAlias($middleware)) return;

            return new $middlewareClass();
        }

        if (class_exists($middleware)) {
            return new $middleware();
        }

        return;
    }

    /**
     * Method responsible for identifying 
     * a middleware by alias.
     * 
     * @param string $alias
     * 
     * @return null|string
     */
    protected function getByAlias(String $alias)
    {
        $middlewares = MiddlewareRoute::getGlobalMiddlewares();

        if (!array_key_exists($alias, $middlewares)) {
            return;
        }

        if (class_exists($middlewares[$alias])) {
            return $middlewares[$alias];
        }

        return;
    }

    /**
     * Method responsible for 
     * calling the next middleware.
     * 
     * @return mixed|bool
     */
    protected function next()
    {
        $this->currentQueueNumber++;

        return $this->callMiddlewares();
    }

    /**
     * Method responsible for resetting 
     * the middleware queue.
     * 
     * @return void
     */
    protected function reset()
    {
        $this->currentQueueNumber = 0;
    }

    /**
     * Method responsible for calling
     * middlewares and class handle.
     * 
     * @return mixed|bool
     */
    protected function callMiddlewares()
    {
        if (!isset($this->queue[$this->currentQueueNumber])) {
            $this->reset();
            return true;
        }

        $currentMiddleware = $this->queue[$this->currentQueueNumber];

        if (is_null($currentMiddleware) || empty($currentMiddleware)) {
            return $this->next();
        }

        return $currentMiddleware->handle(
            $this->currentRequest,
            fn () => $this->next()
        );
    }
}
