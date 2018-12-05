<?php

namespace Bergado\Infrastructure\CommandBus;

use Psr\Container\ContainerInterface;
use League\Tactician\Container\ContainerLocator;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\MethodNameInflector\HandleInflector;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\CommandBus as Tactician;

class CommandBus
{
    protected $inflector;
    protected $extractor;
    protected $locator;
    protected $container;
    protected $commandHandler;
    protected $commandBus;
    protected $handlers;

    public function __construct(HandleInflector $inflector, ClassNameExtractor $extractor, ContainerLocator $locator, ContainerInterface $container, CommandHandlerMiddleware $commandHandler, Tactician $commandBus, $handlers = [])
    {
        $this->inflector = $inflector;
        $this->extractor = $extractor;
        $this->locator = $locator;
        $this->commandHandler = $commandHandler;
        $this->commandBus = $commandBus;
        $this->handlers = $handlers;

    }

    private function setCommandBus()
    {
        $locator = $this->locator($this->container, $this->handlers);
        $commandHandler = $this->commandHandler($this->extractor, $locator, $inflector);
        $this->commandBus = $this->commandBus([$commandHandler]);
        return $this;
    }
}
