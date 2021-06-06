<?php

namespace MinasRouter\Middlewares;

class Middlewares {
    /** @var array */
    protected $middlewares = [];

    /** @var int */
    protected $currentQueueIndex = 0;

    public function __construct(Array $middlewares)
    {
        $this->middlewares = $middlewares;
    }
}