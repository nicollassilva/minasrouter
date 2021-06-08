<?php

namespace MinasRouter\Middlewares;

class Middlewares
{
    /** @var array */
    protected $middlewares = [];

    /** @var int */
    protected $currentQueueIndex = 0;

    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }
}
