<?php

/**
 * APPLICATION ROUTES 
 * 
 * 
 * */

$namespace = "App\\Controller\\";

$map->get('/', $namespace . 'HomeController::index');
$map->get('login', '/login', $namespace . 'LoginController');
$map->post('/login', $namespace . 'LoginController::login');
$map->post('/logout', $namespace . 'LoginController::logout');
$map->get('/users/{user}', $namespace . 'UserController::show');

return $map;
