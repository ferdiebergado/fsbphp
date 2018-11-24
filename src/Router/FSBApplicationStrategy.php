<?php

namespace FSB\Router;

use Psr\Http\Server\MiddlewareInterface;
use League\Route\Strategy\StrategyInterface;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ResponseFactoryInterface;
use League\Route\Http\Exception\NotFoundException;
use FSB\Middleware\NotFoundExceptionHandlerMiddleware;
use League\Route\Http\Exception\MethodNotAllowedException;
use FSB\Middleware\MethodNotAllowedExceptionHandlerMiddleware;

class FSBApplicationStrategy extends ApplicationStrategy implements StrategyInterface
{
    public function getNotFoundDecorator(NotFoundException $exception) : MiddlewareInterface
    {
        return new NotFoundExceptionHandlerMiddleware($exception, $this->getContainer());
    }

    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception) : MiddlewareInterface
    {
        return new MethodNotAllowedExceptionHandlerMiddleware($exception, $this->getContainer());
    }
}
