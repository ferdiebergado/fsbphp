<?php

namespace FSB;

use League\Route\Router as LeagueRouter;

class Router
{
    protected $namespace = "App\\Controller\\";
    protected $router;
    protected $routes;

    public function __construct(LeagueRouter $router, array $routes)
    {
        $this->router = $router;
        $this->routes = $routes;
    }

    public function __invoke() : LeagueRouter
    {
        foreach ($this->routes as $route) {
            $this->router->map($route[0], $route[1], $namespace . $route[2][0] . '::' . $route[2][1]);
        }
        return $this->router;
    }
}
