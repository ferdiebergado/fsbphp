<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use League\Route\Http\Exception\MethodNotAllowedException;

class MethodNotAllowedExceptionHandlerMiddleware extends ExceptionHandlerMiddleware
{
    protected $exception;

    public function __construct(MethodNotAllowedException $exception, ContainerInterface $container)
    {
        parent::__construct($container);
        $this->exception = $exception;
    }
}
