<?php

namespace Bergado\Infrastructure\Container;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    protected $container;

    public function __construct()
    {
        $builder = new ContainerBuilder();
        if (!DEBUG_MODE) {
            $builder->enableCompilation(CACHE_PATH . 'container');
        }
        $builder->useAutowiring(false);
        $builder->useAnnotations(false);
        $dependencies = require(CONFIG_PATH . 'dependencies.php');
        $builder->addDefinitions($dependencies);
        $container = $builder->build();

        $this->container = $container;
    }

    public function get($id)
    {
        return $this->container->get($id);
    }

    public function has($id)
    {
        return $this->container->has($id);
    }

    public function __call($name, $arguments)
    {
        return $this->container->$name($arguments);
    }

}
