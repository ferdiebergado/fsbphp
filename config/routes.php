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
$map->get('login', '/login', [$namespace . 'LoginController', 'show'])->auth(['loggedIn' => false]);
$map->post('login.post', '/login', [$namespace . 'LoginController', 'login'])->auth(['loggedIn' => false]);
$map->post('logout', '/logout', [$namespace . 'LoginController', 'logout'])->auth(['loggedIn' => true]);
$map->get('users', '/users/{user}', [$namespace . 'UserController', 'show'])->auth(['loggedIn' => true]);

return $map;
