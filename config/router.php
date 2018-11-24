<?php

$container = new FSB\Container();
$router = $container->get('router');

/* Create the router dispatcher */
return Fastroute\cachedDispatcher(function (Fastroute\RouteCollector $r) {
    $namespace = "App\\Controller\\";
    $routes = include(CONFIG_PATH . 'routes.php');
    foreach ($routes as $route) {
        $r->addRoute($route[0], $route[1], [$namespace . $route[2][0], $route[2][1]]);
    }
}, [
    'cacheFile' => CACHE_PATH . 'routes' . DS . 'route.cache', /* required */
    'cacheDisabled' => DEBUG_MODE,     /* optional, enabled by default */
]);
