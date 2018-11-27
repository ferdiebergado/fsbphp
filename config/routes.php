<?php

/**
 * APPLICATION ROUTES 
 * 
 * @var string $namespace
 * @var \Aura\Router\Map $map
 * 
 * @return \Aura\Router\Map
 * */

$namespace = "App\\Controller\\";

$map->get('home', '/', [$namespace . 'HomeController', 'index']);
$map->get('login', '/login', [$namespace . 'LoginController', 'show']);
$map->post('login.post', '/login', [$namespace . 'LoginController', 'login']);
$map->post('logout', '/logout', [$namespace . 'LoginController', 'logout']);
$map->get('users', '/users/{user}', [$namespace . 'UserController', 'show']);

return $map;
