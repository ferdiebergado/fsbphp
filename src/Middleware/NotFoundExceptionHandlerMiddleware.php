<?php

namespace FSB\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use League\Route\Http\Exception\NotFoundException;
use Psr\Container\ContainerInterface;

class NotFoundExceptionHandlerMiddleware extends ExceptionHandlerMiddleware
{
    protected $exception;

    public function __construct(NotFoundException $exception, ContainerInterface $container)
    {
        parent::__construct($container);
        $this->exception = $exception;
    }
}
