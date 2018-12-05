<?php

namespace Bergado;

use Psr\Container\ContainerInterface;
use Bergado\Infrastructure\Router\Router;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Bergado\Infrastructure\Container\Container;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Bergado\Core\Application\Service\RouterInterface;
use Bergado\Infrastructure\Exception\ExceptionHandler;
use Bergado\Core\Application\Service\ExceptionHandlerInterface;

class Kernel
{
    protected $container;
    protected $exceptionHandler;
    protected $router;
    protected $request;
    protected $dispatcher;
    protected $emitterStack;
    protected $emitter;
    protected $conditionalEmitter;

    public function __construct()
    {
        $this->setContainer($this->containerFactory());
        $this->setExceptionHandler($this->exceptionHandlerFactory());
        $this->setRouter($this->routerFactory());
        $this->setDispatcher($this->dispatcherFactory());
        $this->setSapiEmitter($this->sapiEmitterFactory());
        $this->setConditionalEmitter($this->conditionalEmitterFactory());
        $this->setEmitterStack($this->emitterStackFactory());
    }

    protected function getContainer()
    {
        return $this->container;
    }

    protected function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        return $this;
    }

    /** Instantiate the DI Container
     * @var Psr\Container\ContainerInterface $c */
    protected function containerFactory()
    {
        return new Container;
    }

    protected function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /** Register the Exception Handler 
    //  * @var \Bergado\Infrastructure\Exception\ExceptionHandlerInterface $exceptionHandler */
    protected function setExceptionHandler(ExceptionHandlerInterface $exceptionHandler)
    {
        $this->exceptionHandler = $exceptionHandler;
        return $this;
    }

    protected function exceptionHandlerFactory()
    {
        return $this->container->get('exception-handler');
    }

    protected function registerExceptionHandler()
    {
        return $this->exceptionHandler->register();
    }

    protected function getRouter()
    {
        return $this->router;
    }

    /** Load the application routes  
    //  * @var \Bergado\Router\Router $router */
    protected function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    protected function routerFactory()
    {
        return $this->container->get('router');
    }

    protected function startRouter()
    {
        return $this->router->start();
    }

    /** Create a Server Request
     * @var Psr\Http\Message\ServerRequestInterface $request */
    protected function createRequest()
    {
        return $this->container->get('request');
    }

    protected function setRequest(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    protected function getRequest()
    {
        return $this->request;
    }

    protected function getDispatcher()
    {
        return $this->dispatcher;
    }

    protected function setDispatcher(RequestHandlerInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    protected function dispatcherFactory()
    {
        return $this->container->get('dispatcher');
    }

    /** Dispatch the middleware stack
     * @var \Relay\Relay $dispatcher
     * @var \Psr\Http\Message\ResponseInterface $response */
    protected function handleRequest(ServerRequestInterface $request)
    {
        return $this->dispatcher->handle($request);
    }

    protected function emitterStackFactory()
    {
        return $this->container->get('emitter-stack');
    }

    protected function sapiEmitterFactory()
    {
        return $this->container->get('emitter');
    }

    protected function getSapiEmitter()
    {
        return $this->emitter;
    }

    protected function setSapiEmitter(EmitterInterface $emitter)
    {
        $this->emitter = $emitter;
        return $this;
    }

    protected function getEmitterStack()
    {
        return $this->emitterStack;
    }

    protected function setEmitterStack(EmitterInterface $emitterStack)
    {
        $this->emitterStack = $emitterStack;
        return $this;
    }

    protected function getConditionalEmitter()
    {
        return $this->conditionalEmitter;
    }

    protected function setConditionalEmitter(EmitterInterface $conditionalEmitter)
    {
        $this->conditionalEmitter = $conditionalEmitter;
        return $this;
    }

    protected function conditionalEmitterFactory()
    {
        return $this->container->get('conditional-emitter');
    }

    protected function buildEmitterStack()
    {
        $this->emitterStack->push($this->emitter);
        $this->emitterStack->push($this->conditionalEmitter);
        return $this;
    }

    public function run()
    {
        $this->registerExceptionHandler();
        $this->startRouter();
        $response = $this->handleRequest($this->createRequest());
        $this->buildEmitterStack();
        return $this->emitterStack->emit($response);
    }

}
