<?php

$container = new FSB\Container();
$router = $container->get('router');
$namespace = "App\\Controller\\";
$routes = include(CONFIG_PATH . 'routes.php');

foreach ($routes as $route) {
    $router->map($route[0], $route[1], $namespace . $route[2][0] . '::' . $route[2][1]);
}

return $router;
